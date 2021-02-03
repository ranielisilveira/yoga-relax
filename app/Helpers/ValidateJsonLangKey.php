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
                    throw new Exception(trans('messages.language_not_accepted') . " ($k) (" . env("LANGUAGES") . ")");
                }
            }

            foreach ($system_languages as $lang) {
                if (!in_array($lang, array_keys(json_decode($json, true)))) {
                    throw new Exception(trans('messages.language_required') . " ($lang). Linguagens obrigatórias: (" . env("LANGUAGES") . ")");
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function valid($str)
    {
        try {
            $system_languages = explode(",", env("LANGUAGES"));
            if (!in_array($str, $system_languages)) {
                throw new Exception(trans('messages.language_not_accepted') . "($str) (" . env("LANGUAGES") . ")");
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
