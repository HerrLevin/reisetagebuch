# Vendored geo data

Two files are derived from the same upstream source:

- `ne_50m_admin_0_countries.geojson` (this directory) — the vendored source of
  truth, consumed by `php artisan app:import-countries` to populate the
  `countries` table (PostGIS polygons + ISO A2 code) for point-in-polygon and
  line-intersects country lookups. All ISO-code resolution, multi-feature
  merging, and overseas-territory filtering happens in
  `app/Console/Commands/ImportCountries.php` at import time — this file itself
  is just the raw upstream data with unused properties stripped.
- `public/countries.geojson` (repo root) — a simplified, pre-resolved copy used
  directly by the frontend map (`resources/js/Components/Maps/CountriesMap.vue`)
  as a static asset. It duplicates `ImportCountries.php`'s resolution/merge/filter
  logic in a throwaway script (see below) because the two files serve different
  consumers (Postgres vs. the browser) and there's no shared code path between
  PHP and the static-asset build — **if `ImportCountries.php`'s logic changes,
  regenerate this file too, or it will drift out of sync with what's stored server-side.**

Source: Natural Earth 1:50m Cultural, "Admin 0 – Countries" (public domain, no
attribution required), via the pre-converted GeoJSON in the `nvkelso/natural-earth-vector`
mirror.

## Regenerating both files

Requires `python3` and `npx` (for `mapshaper`, fetched on demand — no local install needed).

**1. Download the raw source:**

```bash
curl -sL -o /tmp/ne_50m_admin_0_countries.geojson \
  https://raw.githubusercontent.com/nvkelso/natural-earth-vector/master/geojson/ne_50m_admin_0_countries.geojson
```

**2. Trim to `database/data/ne_50m_admin_0_countries.geojson`** — keep only the
properties `ImportCountries.php` actually reads (`ISO_A2`, `ISO_A2_EH`,
`ADM0_A3`, `NAME`); everything else (localized names, map styling hints,
population estimates, ...) is dropped to keep the vendored file small:

```bash
python3 -c "
import json
d = json.load(open('/tmp/ne_50m_admin_0_countries.geojson'))
keep = {'ISO_A2', 'ISO_A2_EH', 'ADM0_A3', 'NAME'}
out = {'type': 'FeatureCollection', 'features': []}
for f in d['features']:
    props = {k: v for k, v in f['properties'].items() if k in keep}
    out['features'].append({'type': 'Feature', 'properties': props, 'geometry': f['geometry']})
json.dump(out, open('database/data/ne_50m_admin_0_countries.geojson', 'w'))
print(len(out['features']), 'features written')
"
```

**3. Re-import into Postgres:** `php artisan app:import-countries` (idempotent,
upserts by `iso_a2`). This is the authoritative pass — it resolves each
feature's ISO A2 code (`ISO_A2` → `ISO_A2_EH` → a small override map for
Kosovo/Somaliland/Northern Cyprus → skip if unresolvable), groups features
that share a resolved code (e.g. Australia + its external territories) into
one `MultiPolygon`, and drops far-flung overseas components for
France/Netherlands (see `MAIN_LANDMASS_ONLY` in `ImportCountries.php`).

**4. Regenerate `public/countries.geojson`** — this mirrors step 3's logic
(resolve → merge → filter) outside the database, then simplifies the geometry
for web delivery:

```bash
python3 -c "
import json, math
from collections import defaultdict

def haversine(a, b):
    R = 6371
    lon1, lat1 = a; lon2, lat2 = b
    phi1, phi2 = math.radians(lat1), math.radians(lat2)
    dphi = math.radians(lat2 - lat1)
    dlambda = math.radians(lon2 - lon1)
    x = math.sin(dphi / 2) ** 2 + math.cos(phi1) * math.cos(phi2) * math.sin(dlambda / 2) ** 2
    return 2 * R * math.asin(min(1, math.sqrt(x)))

def poly_area_and_centroid(ring):
    area = cx = cy = 0
    for i in range(len(ring) - 1):
        x1, y1 = ring[i]; x2, y2 = ring[i + 1]
        cross = x1 * y2 - x2 * y1
        area += cross
        cx += (x1 + x2) * cross
        cy += (y1 + y2) * cross
    area /= 2
    if abs(area) < 1e-9:
        xs = [p[0] for p in ring]; ys = [p[1] for p in ring]
        return 1e-9, (sum(xs) / len(xs), sum(ys) / len(ys))
    return abs(area), (cx / (6 * area), cy / (6 * area))

def filter_main_landmass(polygons, radius_km=1200):
    comps = [(*poly_area_and_centroid(poly[0]), poly) for poly in polygons]
    comps.sort(key=lambda c: -c[0])
    main_centroid = comps[0][1]
    return [poly for _, centroid, poly in comps if haversine(main_centroid, centroid) <= radius_km]

# keep in sync with ImportCountries::ISO_A2_OVERRIDES / MAIN_LANDMASS_ONLY
OVERRIDES = {'KOS': 'XK', 'SOL': 'XS', 'CYN': 'XN'}
MAIN_LANDMASS_ONLY = {'FRA', 'NLD'}
SKIP = {'KAS'}  # Siachen Glacier: disputed, uninhabited

d = json.load(open('database/data/ne_50m_admin_0_countries.geojson'))
groups, names = defaultdict(list), {}
for f in d['features']:
    p = f['properties']
    adm0 = p.get('ADM0_A3')
    code = p.get('ISO_A2')
    if not (isinstance(code, str) and len(code) == 2):
        code = p.get('ISO_A2_EH')
    if not (isinstance(code, str) and len(code) == 2):
        code = OVERRIDES.get(adm0)
    if not (isinstance(code, str) and len(code) == 2) or adm0 in SKIP:
        continue

    geom = f['geometry']
    polys = geom['coordinates'] if geom['type'] == 'MultiPolygon' else [geom['coordinates']]
    if adm0 in MAIN_LANDMASS_ONLY:
        polys = filter_main_landmass(polys)

    groups[code].extend(polys)
    names.setdefault(code, p.get('NAME'))

out = {'type': 'FeatureCollection', 'features': [
    {'type': 'Feature', 'properties': {'iso_a2': code, 'name': names[code]},
     'geometry': {'type': 'MultiPolygon', 'coordinates': polys}}
    for code, polys in groups.items()
]}
json.dump(out, open('/tmp/countries_merged.geojson', 'w'))
print(len(out['features']), 'merged features written')
"

npx --yes mapshaper -i /tmp/countries_merged.geojson -simplify 10% -o public/countries.geojson format=geojson
```

`mapshaper -simplify 10%` shrinks the ~2.5 MB merged file to ~280 KB, which is
what actually gets shipped to the browser.

Disputed-territory boundaries reflect Natural Earth's own editorial choices
(Crimea, Kashmir, Western Sahara, Taiwan, Kosovo, etc.) — a known, accepted
limitation of any off-the-shelf boundary dataset, not something this app
attempts to adjudicate.
