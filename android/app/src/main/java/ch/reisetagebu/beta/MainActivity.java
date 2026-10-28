package ch.reisetagebu.beta;

import android.os.Bundle;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {
    @Override
    public void onCreate(Bundle savedInstanceState) {
        registerPlugin(WebAuthPlugin.class);
        super.onCreate(savedInstanceState);
    }
}
