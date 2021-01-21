<?php

namespace App\Helpers;

use Exception;

class ValidateJsonLangKey
{
    public static function validate($json)
    {
        try {
            $system_languages = explode(",", env("LANGUAGES"));
            foreach (json_decode($json, true) as $k => $v) {
                if (!in_array($k, $system_languages)) {
                    throw new Exception("Linguagem não permitida no sistema. ($k) (" . env("LANGUAGES") . ")");
                }
            }

            foreach ($system_languages as $lang) {
                if (!in_array($lang, array_keys(json_decode($json, true)))) {
                    throw new Exception("Linguagem obrigatória no sistema não informada: ($lang). Linguagens obrigatórias: (" . env("LANGUAGES") . ")");
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
