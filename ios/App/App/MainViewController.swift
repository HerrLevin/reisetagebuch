import Capacitor

// Capacitor's WKWebView doesn't enable the interactive edge-swipe-back
// gesture by default. Since the SPA navigates via pushState (real WebView
// back-forward history), turning this on gives native-style swipe-back for
// free.
class MainViewController: CAPBridgeViewController {
    override func capacitorDidLoad() {
        super.capacitorDidLoad()
        webView?.allowsBackForwardNavigationGestures = true
    }
}
