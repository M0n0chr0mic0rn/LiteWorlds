<?php
class Key{
    function __construct() {}

    function Craft2FA() {
        $key = (object)array("copper"=>"LWQ","jade"=>"LWQ","crystal"=>"LWQ");

        for ($a=0; $a < 3; $a++) { 
            for ($b=0; $b < 128; $b++) { 
                switch ($a) {
                    case 0:$key->copper .= self::RandomSign();break;
                    case 1:$key->jade .= self::RandomSign();break;
                    case 2:$key->crystal .= self::RandomSign();break;
                    default:break;
                }
            }
        }

        return $key;
    }

    function CraftAuth() {
        $result = 'LWQ';

        for ($a=0; $a < 384; $a++) { 
            $result .= self::RandomSign();
        }

        return $result;
    }

    private function RandomSign() {
        $rand = rand(0, 61);

        if ($rand < 10) {
            return $rand;
        }else{
            switch ($rand) {
                case 10:return 'a';break;
                case 11:return 'b';break;
                case 12:return 'c';break;
                case 13:return 'd';break;
                case 14:return 'e';break;
                case 15:return 'f';break;
                case 16:return 'g';break;
                case 17:return 'h';break;
                case 18:return 'i';break;
                case 19:return 'j';break;
                case 20:return 'k';break;
                case 21:return 'l';break;
                case 22:return 'm';break;
                case 23:return 'n';break;
                case 24:return 'o';break;
                case 25:return 'p';break;
                case 26:return 'q';break;
                case 27:return 'r';break;
                case 28:return 's';break;
                case 29:return 't';break;
                case 30:return 'u';break;
                case 31:return 'v';break;
                case 32:return 'w';break;
                case 33:return 'x';break;
                case 34:return 'y';break;
                case 35:return 'z';break;
                case 36:return 'A';break;
                case 37:return 'B';break;
                case 38:return 'C';break;
                case 39:return 'D';break;
                case 40:return 'E';break;
                case 41:return 'F';break;
                case 42:return 'G';break;
                case 43:return 'H';break;
                case 44:return 'I';break;
                case 45:return 'J';break;
                case 46:return 'K';break;
                case 47:return 'L';break;
                case 48:return 'M';break;
                case 49:return 'N';break;
                case 50:return 'O';break;
                case 51:return 'P';break;
                case 52:return 'Q';break;
                case 53:return 'R';break;
                case 54:return 'S';break;
                case 55:return 'T';break;
                case 56:return 'U';break;
                case 57:return 'V';break;
                case 58:return 'W';break;
                case 59:return 'X';break;
                case 60:return 'Y';break;
                case 61:return 'Z';break;
                default:break;
            }
        }
    }
}