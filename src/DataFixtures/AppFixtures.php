<?php

namespace App\DataFixtures;

use App\Entity\Word;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $words = [
            'minefield',
            'narayan',
            'discord',
            'game of thrones',
            'etat des lieux',
            'citoyen',
            'noble',
            'gouverneur',
            'empereur',
            'villageois',
            'paysan',
            'vagabond',
            'chevalier',
            'coraux',
            'tas de sel',
            'chevalierisation',
            'adoubement',
            'ambassadeur',
            'scribe',
            'dokmixer',
            'thorgrin',
            'louvinette',
            'cerise',
            'peche',
            'abricot',
            'tomate',
            'pomme',
            'poire',
            'pasteque',
            'melon',
            'fraise',
            'framboise',
            'tangerine',
            'orange',
            'clementine',
        ];
        
        foreach($words as $word) {
            $w = new Word;
            $w->setWord(strtoupper($word));
            $manager->persist($w);
        }

        $manager->flush();
    }
}
