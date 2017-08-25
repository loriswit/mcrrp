package me.olybri.mcrrp.util;// Created by Loris Witschard on 7/3/2017.

import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import me.olybri.mcrrp.MCRRP;

import java.io.IOException;
import java.lang.reflect.Type;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.Map;

/**
 * Class that provides static functions to translate text to a specific language.
 */
public class Translation
{
    private static Map<String, String> translation;
    
    /**
     * Loads all translations from the language specified in <i>config.yml</i>.
     * The translation file name must match "data/lang/<b>language</b>.json"
     *
     * @throws IOException if the translation file cannot be read.
     */
    public static void init() throws IOException
    {
        String lang = MCRRP.config.getString("settings.lang");
        
        if(lang.equals("en"))
            return;
        
        Path filePath = Paths.get("../data/lang/" + lang + ".json");
        String content = new String(Files.readAllBytes(filePath), StandardCharsets.UTF_8);
        
        Gson gson = new Gson();
        Type type = new TypeToken<Map<String, String>>(){}.getType();
        translation = gson.fromJson(content, type);
    }
    
    /**
     * Returns the translation of the given text. If the first character is uppercase, it will remain so.
     *
     * @param text The text to translate.
     * @return The translated text. If no translation is found, the input text is returned.
     */
    public static String tr(String text)
    {
        if(translation == null)
            return text;
        
        String key = text.toLowerCase();
        
        if(!translation.containsKey(key))
            return text;
        
        if(isUpperFirst(text))
            return upperFirst(translation.get(key));
        
        return translation.get(key);
    }
    
    private static String upperFirst(String str)
    {
        return str.substring(0, 1).toUpperCase() + str.substring(1);
    }
    
    private static boolean isUpperFirst(String str)
    {
        return Character.isUpperCase(str.charAt(0));
    }
}
