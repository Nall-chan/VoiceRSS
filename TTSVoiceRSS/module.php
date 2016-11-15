<?

class TTSVoiceRSS extends IPSModule
{

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

    public function Destroy()
    {

        parent::Destroy();
        if (IPS_GetKernelRunlevel() <> 10103)
            return;
        $MediaID = @IPS_GetObjectIDByIdent('Voice', $this->InstanceID);
        if ($MediaID > 0)
            IPS_DeleteMedia($MediaID, true);
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        if (trim($this->ReadPropertyString('Apikey')) == "")
            $this->SetStatus(104);
        else
            $this->SetStatus(102);
    }

################## PUBLIC
    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:
     */

    public function GenerateFile(string $Text, string $Filename)
    {

        $Format = $this->ReadPropertyString('Sample');
        $Codec = $this->ReadPropertyString('Codec');
        $Language = $this->ReadPropertyString('Language');
        return $this->GenerateFileEx($Text, $Filename, $Format, $Codec, $Language);
    }

    public function GenerateFileEx(string $Text, string $Filename, string $Format, string $Codec, string $Language)
    {
        if ((strpos($Filename, '.' . strtolower($Codec))) === false)
            $Filename .='.' . strtolower($Codec);
        return $this->LoadTTSFile($Text, $Filename, 0, $Format, $Codec, $Language, false);
    }

    public function GetDataContent(string $Text)
    {
        $Format = $this->ReadPropertyString('Sample');
        $Codec = $this->ReadPropertyString('Codec');
        $Language = $this->ReadPropertyString('Language');
        return $this->GetDataContentEx($Text, $Format, $Codec, $Language);
    }

    public function GetDataContentEx(string $Text, string $Format, string $Codec, string $Language)
    {
        return $this->LoadTTSFile($Text, '', 0, $Format, $Codec, $Language, true);
    }

    public function GenerateMediaObject(string $Text, int $MediaID)
    {

        $Format = $this->ReadPropertyString('Sample');
        $Codec = $this->ReadPropertyString('Codec');
        $Language = $this->ReadPropertyString('Language');
        return $this->GenerateMediaObjectEx($Text, $MediaID, $Format, $Codec, $Language);
    }

    public function GenerateMediaObjectEx(string $Text, int $MediaID, string $Format, string $Codec, string $Language)
    {

        if ($MediaID == 0)
            $MediaID = @IPS_GetObjectIDByIdent('Voice', $this->InstanceID);
        if ($MediaID > 0)
        {
            if (IPS_MediaExists($MediaID) === false)
                trigger_error('MediaObject not exists.', E_USER_NOTICE);
            return false;
            if (IPS_GetMedia($MediaID)['MediaType'] <> 2)
                trigger_error('Wrong MediaType', E_USER_NOTICE);
            return false;
        }

        $raw = $this->LoadTTSFile($Text, '', 0, $Format, $Codec, $Language, true);

        if ($raw === false)
            return false;

        if ($MediaID === false)
        {
            $MediaID = IPS_CreateMedia(2);
            IPS_SetMediaCached($MediaID, true);
            IPS_SetName($MediaID, 'Voice');
            IPS_SetParent($MediaID, $this->InstanceID);
            IPS_SetIdent($MediaID, 'Voice');
        }

        $Filename = 'media' . DIRECTORY_SEPARATOR . $MediaID . '.' . strtolower($Codec);

        IPS_SetMediaFile($MediaID, $Filename, False);
        IPS_SetMediaContent($MediaID, base64_encode($raw));
        IPS_SetInfo($MediaID, $Text);
        return $MediaID;
    }

################## PRIVATE    

    protected function LoadTTSFile(string $Text, string $Filename, int $Speed, string $Format, string $Codec, string $Language, bool $raw)
    {
        if (trim($this->ReadPropertyString('Apikey')) == "")
        {
            $this->SetStatus(104);
            $this->SendDebug('Api-Key Error', 'Api-Key not set.', 0);
            trigger_error('Api-Key not set.', E_USER_NOTICE);
            return false;
        }

        if (trim($Text) == '')
        {
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
        if ($http_code >= 400)
        {
            $this->SendDebug('Webrequest Error', $http_code, 0);
            $result = false;
        }
        else
            $this->SendDebug('Webrequest Result', $http_code, 0);
        curl_close($ch);

        if (substr($result, 0, 5) == 'ERROR')
        {
            $this->SendDebug('ERROR', substr($result, 7), 0);
            $result = false;
        }

        if ($result === false)
        {
            trigger_error("Error on get VoiceData", E_USER_NOTICE);
            return false;
        }
        If ($raw)
            return $result;

        try
        {
            $fh = fopen($Filename, 'w');
            fwrite($fh, $result);
        }
        catch (Exception $exc)
        {
            @fclose($fh);
            trigger_error($exc->getMessage(), E_USER_NOTICE);
            return false;
        }
        fclose($fh);
        return true;
    }

}

?>