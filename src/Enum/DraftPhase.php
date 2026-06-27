<?php

namespace App\Enum;

enum DraftPhase: string
{
    // Bans
    case BlueBan1 = 'blue_ban_1';
    case BlueBan2 = 'blue_ban_2';
    case BlueBan3 = 'blue_ban_3';
    case BlueBan4 = 'blue_ban_4';
    case BlueBan5 = 'blue_ban_5';
    case RedBan1 = 'red_ban_1';
    case RedBan2 = 'red_ban_2';
    case RedBan3 = 'red_ban_3';
    case RedBan4 = 'red_ban_4';
    case RedBan5 = 'red_ban_5';
    
    // Picks
    case BluePick1 = 'blue_pick1';
    case BluePick2 = 'blue_pick2';
    case BluePick3 = 'blue_pick3';
    case BluePick4 = 'blue_pick4';
    case BluePick5 = 'blue_pick5';
    case RedPick1 = 'red_pick1';
    case RedPick2 = 'red_pick2';
    case RedPick3 = 'red_pick3';
    case RedPick4 = 'red_pick4';
    case RedPick5 = 'red_pick5';

    /**
     * @return list<self>
     */
    public static function ordered(): array
    {
        return [
            self::BlueBan1,
            self::RedBan1,
            self::BlueBan2,
            self::RedBan2,
            self::BlueBan3,
            self::RedBan3,
            self::BluePick1,
            self::RedPick1,
            self::RedPick2,
            self::BluePick2,
            self::BluePick3,
            self::RedPick3,
            self::BlueBan4,
            self::RedBan4,
            self::BlueBan5,
            self::RedBan5,
            self::RedPick4,
            self::BluePick4,
            self::BluePick5,
            self::RedPick5,
        ];
    }

    public function getSide(): DraftSide
    {
        if (\in_array(
            $this,
            [
                self::BlueBan1,
                self::BlueBan2,
                self::BlueBan3,
                self::BlueBan4,
                self::BlueBan5,
                self::BluePick1,
                self::BluePick2,
                self::BluePick3,
                self::BluePick4,
                self::BluePick5,
            ],
            strict: true,
        )) {
            return DraftSide::Blue;
        }

        return DraftSide::Red;
    }

    public function getPosition(): int
    {
        if (\in_array($this, [self::BlueBan1, self::RedBan1, self::BluePick1, self::RedPick1], strict: true)) {
            return 1;
        }

        if (\in_array($this, [self::BlueBan2, self::RedBan2, self::BluePick2, self::RedPick2], strict: true)) {
            return 2;
        }

        if (\in_array($this, [self::BlueBan3, self::RedBan3, self::BluePick3, self::RedPick3], strict: true)) {
            return 3;
        }

        if (\in_array($this, [self::BlueBan4, self::RedBan4, self::BluePick4, self::RedPick4], strict: true)) {
            return 4;
        }

        return 5;
    }

    public function getNext(): self
    {
        $pos = \array_search($this, self::ordered(), strict: true);

        if (false === $pos) {
            return self::BlueBan1;
        }

        return self::ordered()[$pos + 1] ?? self::RedPick5;
    }
}
