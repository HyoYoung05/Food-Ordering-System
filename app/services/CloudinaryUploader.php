<?php
declare(strict_types=1);

final class CloudinaryUploader
{
    public function upload(string $temporaryPath): string
    {
        $cloudName=Env::get('CLOUDINARY_CLOUD_NAME','');$apiKey=Env::get('CLOUDINARY_API_KEY','');$apiSecret=Env::get('CLOUDINARY_API_SECRET','');
        if($cloudName===''||$apiKey===''||$apiSecret==='')throw new RuntimeException('Cloudinary is not configured. Check the CLOUDINARY settings in .env.');
        if(!function_exists('curl_init'))throw new RuntimeException('PHP cURL is required for Cloudinary uploads. Enable extension=curl in XAMPP php.ini.');
        $timestamp=time();$folder='food_ordering_system/products';$signature=sha1("folder={$folder}&timestamp={$timestamp}{$apiSecret}");
        $curl=curl_init("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload");
        curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>45,CURLOPT_POSTFIELDS=>['file'=>new CURLFile($temporaryPath),'api_key'=>$apiKey,'timestamp'=>(string)$timestamp,'folder'=>$folder,'signature'=>$signature]]);
        $response=curl_exec($curl);$status=(int)curl_getinfo($curl,CURLINFO_HTTP_CODE);$curlError=curl_error($curl);curl_close($curl);
        if($response===false)throw new RuntimeException('Cloudinary upload failed: '.$curlError);
        $result=json_decode($response,true);
        if($status<200||$status>=300||empty($result['secure_url']))throw new RuntimeException($result['error']['message']??'Cloudinary rejected the image upload.');
        return (string)$result['secure_url'];
    }
}
