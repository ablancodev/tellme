<?php
class TellmeIA {
    public static function chatGPT($prompt) {
        
        $url = 'https://api.openai.com/v1/chat/completions';


        // hacemos la llamada a la API
        $curl = curl_init();
        $fields = array(
            'model' => 'gpt-3.5-turbo',
            "messages" => array(
                array("role" => "system", "content" => $prompt
                )
            ),
            'max_tokens' => 750,
            'temperature' => 0.5
        );
        $json_string = json_encode($fields);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_string);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization: Bearer ' . get_option('tellme_apikey') ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
        $data = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);


        
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $response = json_decode($data, true);
            return $response['choices'][0]['message']['content'];
        }
    }

    public static function generateImage($prompt) {
        /*
        curl https://api.openai.com/v1/images/generations \
        -H "Content-Type: application/json" \
        -H "Authorization: Bearer $OPENAI_API_KEY" \
        -d '{
            "model": "dall-e-3",
            "prompt": "a white siamese cat",
            "n": 1,
            "size": "1024x1024"
        }'
        */
        $url = 'https://api.openai.com/v1/images/generations';
    
        // hacemos la llamada a la API
        $curl = curl_init();
        $fields = array(
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024'
        );
        $json_string = json_encode($fields);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_string);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization: Bearer ' . get_option('tellme_apikey')));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
    
    
        $data = curl_exec($curl);
        curl_close($curl);
    
        //var_dump($data);
    
        $response = json_decode($data, true);
    
        //var_dump($response);
    
        // image_url = response.data[0].url
        return $response['data'][0]['url'];
    }

    /**
     * audio_to_text
     * curl --request POST \
     * --url https://api.openai.com/v1/audio/transcriptions \
     * --header 'Authorization: Bearer TOKEN' \
     * --header 'Content-Type: multipart/form-data' \
     * --form file=@/path/to/file/openai.mp3 \
     * --form model=whisper-1
     */
    public static function audioToText($file) {
        $url = 'https://api.openai.com/v1/audio/transcriptions';
    
        // hacemos la llamada a la API
        $curl = curl_init();
        $fields = array(
            'model' => 'whisper-1',
            'file' => new CURLFile($file)
        );
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . get_option('tellme_apikey')));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
    
    
        $data = curl_exec($curl);
        curl_close($curl);

        //error_log("Respuesta de whisper:");
        //error_log(print_r($data, true));
    
        if ( !($data) ) {
            return "";
        } else {
            $response = json_decode($data, true);
            return $response['text'];
        }
    }
}
