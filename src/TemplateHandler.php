<?php

namespace Rahmentemplate;

class TemplateHandler
{
    function initTemplateHandler($content)
    {
        $username = 'test';
        $password = 'test';

        $template = get_post_meta(get_the_ID(), 'rahmentemplate_settings_input_templates_field', true);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $template,
            CURLOPT_USERPWD=> $username . ":" . $password,
            CURLOPT_HTTPHEADER => array('Accept-Encoding: gzip'),
    ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'cURL error: ' . curl_error($curl);
        } elseif (substr($response, 0, 2) === "\x1f\x8b") {
            echo gzdecode($response);
            exit;
        } else {
            $decodedResponse = json_decode($response, true);
            if ($decodedResponse !== null) {
                header('Content-Type: application/json');
                echo json_encode($decodedResponse, JSON_PRETTY_PRINT);
            } else {
                echo $response;
            }

        }
        curl_close($curl);

    

        return $content;
    }

}