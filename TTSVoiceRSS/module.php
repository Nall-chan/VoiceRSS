<?php

/**
 * @addtogroup ttsvoicerss
 * @{
 *
 * @package       TTSVoiceRSS
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.01
 */

/**
 * TTSVoiceRSS ist die Klasse für das IPS-Modul 'TTS VoiceRSS'.
 * Erweitert IPSModule
 *
 */
class TTSVoiceRSS extends IPSModule
{

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('Apikey', '');
        $this->RegisterPropertyString('Language', 'de-de');
        $this->RegisterPropertyString('Codec', 'MP3');
        $this->RegisterPropertyString('Sample', '8khz_8bit_mono');
        IPS_SetInfo($this->InstanceID, 'Register at http://www.voicerss.org/');
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Destroy()
    {
        if (IPS_GetKernelRunlevel() <> 10103) {
            return;
        }
        $MediaID = @IPS_GetObjectIDByIdent('Voice', $this->InstanceID);
        if ($MediaID > 0) {
            IPS_DeleteMedia($MediaID, true);
        }
        parent::Destroy();
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        if (trim($this->ReadPropertyString('Apikey')) == "") {
            $this->SetStatus(104);
        } else {
            $this->SetStatus(102);
        }

        if (trim($this->ReadPropertyString('Apikey')) <> $this->ReadPropertyString('Apikey')) {
            @IPS_SetProperty($this->InstanceID, 'Apikey', trim($this->ReadPropertyString('Apikey')));
            @IPS_ApplyChanges($this->InstanceID);
        }
    }

    ################## PUBLIC

    /**
     * IPS-Instanz-Funktion 'TTSV_GenerateFile'
     * Erzeugt eine Audiodatei.
     *
     * @param string $Text Der zu erzeugende Text
     * @param string $Filename Der Dateiname in dem abgespeichert wird.
     * @return bool True bei Erflog, sonst false.
     */
    public function GenerateFile(string $Text, string $Filename)
    {
        $Format = $this->ReadPropertyString('Sample');
        $Codec = $this->ReadPropertyString('Codec');
        $Language = $this->ReadPropertyString('Language');
        return $this->GenerateFileEx($Text, $Filename, $Format, $Codec, $Language);
    }

    /**
     * IPS-Instanz-Funktion 'TTSV_GenerateFileEx'
     * Erzeugt eine Audiodatei.
     *
     * @param string $Text Der zu erzeugende Text
     * @param string $Filename Der Dateiname in dem abgespeichert wird.
     * @param string $Format Das Ziel-Format
     * @param string $Codec Der Ziel-Codec
     * @param string $Language Die zu verwendene Sprache
     * @return bool True bei Erflog, sonst false.
     */
    public function GenerateFileEx(string $Text, string $Filename, string $Format, string $Codec, string $Language)
    {
        if ((strpos($Filename, '.' . strtolower($Codec))) === false) {
            $Filename .='.' . strtolower($Codec);
        }
        return $this->LoadTTSFile($Text, $Filename, 0, $Format, $Codec, $Language, false);
    }

    /**
     * IPS-Instanz-Funktion 'TTSV_GetDataContent'
     * Erzeugt Rohdaten zur weiterverarbeitung.
     *
     * @param string $Text Der zu erzeugende Text
     * @return string|bool Die Rohdaten der Sprachdatei. False im Fehlerfall.
     */
    public function GetDataContent(string $Text)
    {
        $Format = $this->ReadPropertyString('Sample');
        $Codec = $this->ReadPropertyString('Codec');
        $Language = $this->ReadPropertyString('Language');
        return $this->GetDataContentEx($Text, $Format, $Codec, $Language);
    }

    /**
     * IPS-Instanz-Funktion 'TTSV_GetDataContentEx'
     * Erzeugt Rohdaten zur weiterverarbeitung.
     *
     * @param string $Text Der zu erzeugende Text
     * @param string $Format Das Ziel-Format
     * @param string $Codec Der Ziel-Codec
     * @param string $Language Die zu verwendene Sprache
     * @return string|bool Die Rohdaten der Sprachdatei. False im Fehlerfall.
     */
    public function GetDataContentEx(string $Text, string $Format, string $Codec, string $Language)
    {
        return $this->LoadTTSFile($Text, '', 0, $Format, $Codec, $Language, true);
    }

    /**
     * IPS-Instanz-Funktion 'TTSV_GenerateMediaObject'
     * Erzeugt/befüllt ein MedienObject im logischen Baum von IPS.
     *
     * @param string $Text Der zu erzeugende Text
     * @param int $MediaID IPS-ID des zu befüllenden Media-Objektes.
     * @return int|bool Die ID des befüllten Media-Objektes. False im Fehlerfall.
     */
    public function GenerateMediaObject(string $Text, int $MediaID)
    {
        $Format = $this->ReadPropertyString('Sample');
        $Codec = $this->ReadPropertyString('Codec');
        $Language = $this->ReadPropertyString('Language');
        return $this->GenerateMediaObjectEx($Text, $MediaID, $Format, $Codec, $Language);
    }

    /** IPS-Instanz-Funktion 'TTSV_GenerateMediaObjectEx'
     * Erzeugt/befüllt ein MedienObject im logischen Baum von IPS.
     *
     * @param string $Text Der zu erzeugende Text
     * @param int $MediaID IPS-ID des zu befüllenden Media-Objektes.
     * @param string $Format Das Ziel-Format
     * @param string $Codec Der Ziel-Codec
     * @param string $Language Die zu verwendene Sprache
     * @return int|bool Die ID des befüllten Media-Objektes. False im Fehlerfall.
     */
    public function GenerateMediaObjectEx(string $Text, int $MediaID, string $Format, string $Codec, string $Language)
    {
        if ($MediaID == 0) {
            $MediaID = @IPS_GetObjectIDByIdent('Voice', $this->InstanceID);
        }
        if ($MediaID > 0) {
            if (IPS_MediaExists($MediaID) === false) {
                trigger_error('MediaObject not exists.', E_USER_NOTICE);
            }
            return false;
            if (IPS_GetMedia($MediaID)['MediaType'] <> 2) {
                trigger_error('Wrong MediaType', E_USER_NOTICE);
            }
            return false;
        }

        $raw = $this->LoadTTSFile($Text, '', 0, $Format, $Codec, $Language, true);

        if ($raw === false) {
            return false;
        }

        if ($MediaID === false) {
            $MediaID = IPS_CreateMedia(2);
            IPS_SetMediaCached($MediaID, true);
            IPS_SetName($MediaID, 'Voice');
            IPS_SetParent($MediaID, $this->InstanceID);
            IPS_SetIdent($MediaID, 'Voice');
        }

        $Filename = 'media' . DIRECTORY_SEPARATOR . $MediaID . '.' . strtolower($Codec);

        IPS_SetMediaFile($MediaID, $Filename, false);
        IPS_SetMediaContent($MediaID, base64_encode($raw));
        IPS_SetInfo($MediaID, $Text);
        return $MediaID;
    }

    ################## PRIVATE

    /**
     * Übergibt den Text an VoiceRSS und liefert das Ergebnis als String oder Datei.
     *
     * @param string $Text Der zu erzeugende Text
     * @param string $Filename Der Dateiname in dem abgespeichert wird.
     * @param int $Speed Die Sprachgeschwindigkeit.
     * @param string $Format Das Ziel-Format
     * @param string $Codec Der Ziel-Codec
     * @param string $Language Die zu verwendene Sprache
     * @param bool $raw True wenn Rohdaten zurückgegeben werden sollen.
     * @return string|bool Die Rohdaten der Sprachdatei wenn $raw = true sonst True/False im Erfolg oder Fehlerfall.
     */
    private function LoadTTSFile(string $Text, string $Filename, int $Speed, string $Format, string $Codec, string $Language, bool $raw)
    {
        if (trim($this->ReadPropertyString('Apikey')) == "") {
            $this->SetStatus(104);
            $this->SendDebug('Api-Key Error', 'Api-Key not set.', 0);
            trigger_error('Api-Key not set.', E_USER_NOTICE);
            return false;
        }

        if (trim($Text) == '') {
            trigger_error('Text is empty', E_USER_NOTICE);
            return false;
        }

        $ApiData['key'] = $this->ReadPropertyString('Apikey');

        $ApiData['src'] = $Text;
        $ApiData['hl'] = $Language;
        $ApiData['r'] = $Speed;
        $ApiData['c'] = $Codec;
        $ApiData['f'] = $Format;

        $header[] = "Accept: */*";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: close";
        $header[] = "Accept-Charset: UTF-8";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.voicerss.org/");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $ApiData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 3000);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3000);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $this->SendDebug('DoWebrequest', $Text, 0);
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code >= 400) {
            $this->SendDebug('Webrequest Error', $http_code, 0);
            $result = false;
        } else {
            $this->SendDebug('Webrequest Result', $http_code, 0);
        }
        curl_close($ch);

        if (substr($result, 0, 5) == 'ERROR') {
            $this->SendDebug('ERROR', substr($result, 7), 0);
            $result = false;
        }

        if ($result === false) {
            trigger_error("Error on get VoiceData", E_USER_NOTICE);
            return false;
        }
        if ($raw) {
            return $result;
        }

        try {
            $fh = fopen($Filename, 'w');
            fwrite($fh, $result);
        } catch (Exception $exc) {
            @fclose($fh);
            trigger_error($exc->getMessage(), E_USER_NOTICE);
            return false;
        }
        fclose($fh);
        return true;
    }
}

/** @} */
