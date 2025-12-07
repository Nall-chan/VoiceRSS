[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FVoiceRSS%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-tts-von-voicerss/38137)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FVoiceRSS%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)  
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Check Style](https://github.com/Nall-chan/VoiceRSS/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/VoiceRSS/actions)
[![Run Tests](https://github.com/Nall-chan/VoiceRSS/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/VoiceRSS/actions)  
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#6-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#6-spenden)  

# Symcon-Modul: VoiceRSS <!-- omit in toc -->

Online-TTS Engine von VoiceRSS in IPS nutzen.  
Free bei max. 350 Anfragen pro Tag.  

## Inhaltsverzeichnis <!-- omit in toc -->

- [1. Funktionsumfang](#1-funktionsumfang)
- [2. Voraussetzungen](#2-voraussetzungen)
- [3. Installation \& Konfiguration](#3-installation--konfiguration)
- [4. Funktionsreferenz](#4-funktionsreferenz)
- [5. Anhang](#5-anhang)
- [6. Spenden](#6-spenden)
- [7. Lizenz](#7-lizenz)

## 1. Funktionsumfang

 Über die API von VoiceRSS wird ein Text in das gesprochene Wort überführt.  

 Dieses Modul ermöglicht es, die von VoiceRSS erzeugten Audio-Daten in verschiedener Art zu nutzen.  
 Es kann eine entsprechende Audio-Datei erzeugt werden, oder ein IPS-MedienObjekt verwendet werden.  
 Des weiteren können auch Roh-Daten erzeugt werden.  

## 2. Voraussetzungen

- IP-Symcon ab Version 8.1  
- Registrierung bei [VoiceRSS](http://www.voicerss.org/)  

## 3. Installation & Konfiguration

- Installation  
  Über den `Module-Store` in IPS das Modul `VoiceRSS` hinzufügen.  
  **Bei kommerzieller Nutzung (z.B. als Errichter oder Integrator) wenden Sie sich bitte an den Autor.**  

- Instanz erstellen  
        Im Dialog Instanz hinzufügen, ist das Modul unter dem Hersteller VoiceRSS zu finden.  

- Konfiguration  
        Der persönliche API-Key muss in der Instanz eingetragen werden.  
        Die restlichen Einstellungen sind die Default-Werte für die Instanz-Funktionen.  

## 4. Funktionsreferenz

```php
boolean TTSV_GenerateFile(integer $InstanceID, string $Text, string $Filename);
boolean TTSV_GenerateFileEx(integer $InstanceID, string $Text, string $Filename, string $Format, string $Codec, string $Language, int $Speed, string $Voice)
```

 Erzeugt eine Audiodatei.  
 Wird kein absoluter Pfad bei `$Filename` angegeben, so wird die Datei im Script-Ordner von IPS gespeichert.
 Wird keine korrekte Dateiendung übergeben, so wird Diese ergänzt.
 Die Funktionen liefern `True` bei Erfolg.  

---  

```php
string TTSV_GetDataContent(integer $InstanceID, string $Text);
string TTSV_GetDataContentEx(integer $InstanceID, string $Text, string $Format, string $Codec, string $Language, int $Speed, string $Voice)
```

 Erzeugt Rohdaten zur weiterverarbeitung.  
 Im Fehlerfall wird false zurückgegeben.  

 Beispiel: In ein Medienobjekt schreiben  

   ```php
// Daten holen und in $data speichern.
$data = @TTSV_GetDataContent(40811,"Hallo Welt.");
if ($data === false){
    die("Konnte Daten nicht laden");
}
$MediaID =IPS_CreateMedia(2);
IPS_SetMediaFile($MediaID, "Test.mp3", false);
// Inhalt von $data in das MedienObject schreiben.
IPS_SetMediaContent($MediaID,base64_encode($data));
IPS_SetName($MediaID, "Test");
```  

---  

```php
integer TTSV_GenerateMediaObject(integer $InstanceID, string $Text, integer $MediaID);
integer TTSV_GenerateMediaObjectEx(integer $InstanceID, string $Text, integer $MediaID, string $Format, string $Codec, string $Language, int $Speed, string $Voice)
```

Erzeugt / befüllt ein MedienObject im logischen Baum von IPS:  

- Wird als `$MediaID` eine ID eines vorhandenes MedienObject übergeben, so wird Dieses mit den Audiodaten gefüllt.  
- Wird als $MediaID eine `0` übergeben, so wird unterhalb der VoiceRSS-Instanz ein MedienObject verwendet.  

Der Rückgabewert ist die ID des befüllten Media-Objektes.  
Oder false im Fehlerfall.  

## 5. Anhang

**GUID:**  
 `{133A6F0D-464E-4FAD-8620-02DB0AB9BFD1}`

**Konfiguration:**

| Eigenschaft |  Typ   |  Standardwert  |          Funktion           |
| :---------: | :----: | :------------: | :-------------------------: |
|   Apikey    | string |                |    Api-Key von VoiceRSS     |
|  Language   | string |     de-de      |           Sprache           |
|    Speed    |  int   |       0        | Geschwindigkeit -10 bis +10 |
|    Voice    | string |                |           Stimme            |
|    Codec    | string |      MP3       |        Audio-Format         |
|   Sample    | string | 8khz_8bit_mono |         Samplerate          |

Erlaubte Parameter siehe:
[VoiceRSS API](http://www.voicerss.org/api/documentation.aspx)

**Changelog:**  

**Version 2.22:**  

- Version für IPS 8.1.  

**Version 2.21:**  

- Version für IPS 7.0.  
  
**Version 2.2:**  

- Sprechgeschwindigkeit und Stimme in der Konfiguration ergänzt.  
- Alle ...Ex Funktionen erwarten jetzt Speed und Voice als Parameter.  
  
**Version 2.1:**  

- Fehler in der Fehlerbehandlung behoben.  

**Version 2.0:**  

- Release für IPS 5.1 und den Module-Store  

**Version 1.01:**  

- Doku ergänzt.  

**Version 1.0:**  

- Erstes Release  

## 6. Spenden  
  
  Die Library ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:  

  PayPal:  
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)  

  Wunschliste:  
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)  

## 7. Lizenz

  [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
