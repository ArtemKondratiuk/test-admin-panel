<?php

namespace App\DataFixtures;

use App\Entity\Item;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $items = [
            'Шини',
            'Скло',
            'Двері',
            'Двигун',
            'Фари',
            'Кермо',
            'Гальма',
            'Акумулятор',
            'Радіатор'
        ];

        $reversedItems = array_reverse($items);

        foreach ($reversedItems as $index => $title) {
            $item = new Item();
            $item->setTitle($title);

            $item->setPosition($index + 1);

            $manager->persist($item);
        }

        $manager->flush();
    }
}
