import AuthenticationServices
import Capacitor

// Capacitor's WKWebView doesn't enable the interactive edge-swipe-back
// gesture by default. Since the SPA navigates via pushState (real WebView
// back-forward history), turning this on gives native-style swipe-back for
// free.
class MainViewController: CAPBridgeViewController {
    override func capacitorDidLoad() {
        super.capacitorDidLoad()
        webView?.allowsBackForwardNavigationGestures = true
        bridge?.registerPluginInstance(WebAuthPlugin())
    }
}

// Runs the OAuth login in ASWebAuthenticationSession: iOS asks "… wants to use
// … to sign in", shows an in-app browser sharing Safari's cookies, and hands
// the callback URL straight back instead of going through a deep link.
@objc(WebAuthPlugin)
public class WebAuthPlugin: CAPPlugin, CAPBridgedPlugin, ASWebAuthenticationPresentationContextProviding {
    public let identifier = "WebAuthPlugin"
    public let jsName = "WebAuth"
    public let pluginMethods: [CAPPluginMethod] = [
        CAPPluginMethod(name: "authenticate", returnType: CAPPluginReturnPromise),
    ]
    private var session: ASWebAuthenticationSession?

    @objc func authenticate(_ call: CAPPluginCall) {
        guard let url = call.getString("url").flatMap(URL.init(string:)),
              let scheme = call.getString("callbackScheme") else {
            call.reject("url and callbackScheme are required")
            return
        }

        DispatchQueue.main.async {
            self.session = ASWebAuthenticationSession(url: url, callbackURLScheme: scheme) { callbackURL, error in
                self.session = nil
                if let callbackURL {
                    call.resolve(["url": callbackURL.absoluteString])
                } else if (error as? ASWebAuthenticationSessionError)?.code == .canceledLogin {
                    call.reject("Login cancelled", "CANCELLED")
                } else {
                    call.reject(error?.localizedDescription ?? "Login failed")
                }
            }
            self.session?.presentationContextProvider = self
            self.session?.start()
        }
    }

    public func presentationAnchor(for session: ASWebAuthenticationSession) -> ASPresentationAnchor {
        bridge?.webView?.window ?? ASPresentationAnchor()
    }
}
