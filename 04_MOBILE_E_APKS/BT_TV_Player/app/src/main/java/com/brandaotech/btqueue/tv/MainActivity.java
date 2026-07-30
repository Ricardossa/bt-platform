package com.brandaotech.btqueue.tv;

import android.content.Context;
import android.content.DialogInterface;
import android.content.SharedPreferences;
import android.os.Build;
import android.os.Bundle;
import android.speech.tts.TextToSpeech;
import android.view.View;
import android.view.WindowManager;
import android.view.WindowInsets;
import android.view.WindowInsetsController;
import android.webkit.JavascriptInterface;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.EditText;
import android.widget.Toast;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import java.util.Locale;

public class MainActivity extends AppCompatActivity implements TextToSpeech.OnInitListener {

    private static final String PREFS_NAME = "BT_TV_PREFS";
    private static final String KEY_URL = "server_url";
    private WebView webView;
    private SharedPreferences prefs;
    private TextToSpeech tts;
    private boolean ttsReady = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
        // Prevent screen from sleeping
        getWindow().addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON);
        
        setContentView(R.layout.activity_main);

        webView = findViewById(R.id.webview);
        prefs = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        
        // Initialize TextToSpeech
        tts = new TextToSpeech(this, this);

        setupWebView();
        hideSystemUI();

        String savedUrl = prefs.getString(KEY_URL, "");
        if (savedUrl.isEmpty()) {
            showUrlDialog();
        } else {
            loadUrl(savedUrl);
        }
    }

    private void setupWebView() {
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setDatabaseEnabled(true);
        
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.JELLY_BEAN_MR1) {
            settings.setMediaPlaybackRequiresUserGesture(false);
        }
        
        // Add Javascript Interface for Native Voice
        webView.addJavascriptInterface(new WebAppInterface(), "AndroidVoz");
        
        webView.setWebViewClient(new WebViewClient());
    }

    private void loadUrl(String url) {
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
            url = "http://" + url;
        }
        webView.loadUrl(url);
    }

    private void showUrlDialog() {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("Configurar TV");
        builder.setMessage("Digite o IP ou URL da TV (Ex: 192.168.1.100/painel_v4/public/tv_v2.php)");

        final EditText input = new EditText(this);
        input.setText(prefs.getString(KEY_URL, ""));
        builder.setView(input);

        builder.setPositiveButton("Salvar", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                String url = input.getText().toString().trim();
                if (!url.isEmpty()) {
                    prefs.edit().putString(KEY_URL, url).apply();
                    loadUrl(url);
                } else {
                    Toast.makeText(MainActivity.this, "URL Inválida", Toast.LENGTH_SHORT).show();
                    showUrlDialog();
                }
            }
        });

        builder.setCancelable(false);
        builder.show();
    }

    private void hideSystemUI() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
            WindowInsetsController controller = getWindow().getInsetsController();
            if (controller != null) {
                controller.hide(WindowInsets.Type.statusBars() | WindowInsets.Type.navigationBars());
                controller.setSystemBarsBehavior(WindowInsetsController.BEHAVIOR_SHOW_TRANSIENT_BARS_BY_SWIPE);
            }
        } else {
            getWindow().getDecorView().setSystemUiVisibility(
                    View.SYSTEM_UI_FLAG_IMMERSIVE_STICKY
                            | View.SYSTEM_UI_FLAG_LAYOUT_STABLE
                            | View.SYSTEM_UI_FLAG_LAYOUT_HIDE_NAVIGATION
                            | View.SYSTEM_UI_FLAG_LAYOUT_FULLSCREEN
                            | View.SYSTEM_UI_FLAG_HIDE_NAVIGATION
                            | View.SYSTEM_UI_FLAG_FULLSCREEN);
        }
    }

    @Override
    public void onInit(int status) {
        if (status == TextToSpeech.SUCCESS) {
            int result = tts.setLanguage(new Locale("pt", "BR"));
            if (result == TextToSpeech.LANG_MISSING_DATA || result == TextToSpeech.LANG_NOT_SUPPORTED) {
                Toast.makeText(this, "Idioma Português não suportado no motor TTS", Toast.LENGTH_LONG).show();
            } else {
                ttsReady = true;
            }
        } else {
            Toast.makeText(this, "Falha ao inicializar motor de voz", Toast.LENGTH_LONG).show();
        }
    }

    /**
     * Interface to be called from Javascript
     */
    public class WebAppInterface {
        @JavascriptInterface
        public void falar(String text) {
            if (ttsReady && tts != null) {
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
                    tts.speak(text, TextToSpeech.QUEUE_FLUSH, null, "ID_QUEUEMASTER");
                } else {
                    tts.speak(text, TextToSpeech.QUEUE_FLUSH, null);
                }
            }
        }
        
        @JavascriptInterface
        public void cancelar() {
            if (tts != null) {
                tts.stop();
            }
        }
    }

    @Override
    public void onWindowFocusChanged(boolean hasFocus) {
        super.onWindowFocusChanged(hasFocus);
        if (hasFocus) {
            hideSystemUI();
        }
    }

    @Override
    protected void onDestroy() {
        if (tts != null) {
            tts.stop();
            tts.shutdown();
        }
        super.onDestroy();
    }
}
