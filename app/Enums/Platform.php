<?php

namespace App\Enums;

enum Platform: string
{
    case Pc = 'pc';
    case PlayStation5 = 'ps5';
    case PlayStation4 = 'ps4';
    case PlayStation3 = 'ps3';
    case PlayStation2 = 'ps2';
    case PlayStation1 = 'ps1';
    case PlayStationPortable = 'psp';
    case XboxSeries = 'xbox-series';
    case XboxOne = 'xbox-one';
    case Xbox360 = 'xbox-360';
    case NintendoSwitch = 'switch';
    case WiiU = 'wii-u';
    case Wii = 'wii';
    case Nintendo3ds = '3ds';
    case NintendoDs = 'ds';
    case Nintendo64 = 'n64';
    case SuperNintendo = 'snes';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Pc => 'PC',
            self::PlayStation5 => 'PlayStation 5',
            self::PlayStation4 => 'PlayStation 4',
            self::PlayStation3 => 'PlayStation 3',
            self::PlayStation2 => 'PlayStation 2',
            self::PlayStation1 => 'PlayStation 1',
            self::PlayStationPortable => 'PSP',
            self::XboxSeries => 'Xbox Series X|S',
            self::XboxOne => 'Xbox One',
            self::Xbox360 => 'Xbox 360',
            self::NintendoSwitch => 'Nintendo Switch',
            self::WiiU => 'Wii U',
            self::Wii => 'Wii',
            self::Nintendo3ds => 'Nintendo 3DS',
            self::NintendoDs => 'Nintendo DS',
            self::Nintendo64 => 'Nintendo 64',
            self::SuperNintendo => 'Super Nintendo',
            self::Other => 'Outro',
        };
    }
}
