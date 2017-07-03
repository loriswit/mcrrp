package me.olybri.mcrrp;// Created by Loris Witschard on 7/3/2017.

import com.google.common.reflect.TypeToken;
import com.google.gson.Gson;

import java.io.IOException;
import java.lang.reflect.Type;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.Map;

public class Tr
{
    private static Map<String, String> translation;
    
    public static void load(String lang) throws IOException
    {
        if(lang.equals("en"))
            return;
        
        Path filePath = Paths.get("../lang/" + lang + ".json");
        String content = new String(Files.readAllBytes(filePath), StandardCharsets.UTF_8);
        
        Gson gson = new Gson();
        Type type = new TypeToken<Map<String, String>>(){}.getType();
        translation = gson.fromJson(content, type);
    }
    
    public static String s(String text)
    {
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
