<?php

namespace App\DataFixtures\Country;

use App\Entity\Country\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CountryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $countries = [
            ['FRANCE', 'FR', 1.2000],
            ['BELGIUM', 'BE', 1.2100],
            ['DEUTSCHLAND', 'DE', 1.1900],
            ['LUXEMBOURG', 'LU', 1.1700],
            ['OSTERREICH', 'AT', 1.2000],
            ['GREAT-BRITAIN', 'GB', 1.2000],
            ['ESPANA', 'ES', 1.2100],
            ['SWITZERLAND', 'CH', 1.0770],
            ['NEDERLAND', 'NL', 1.2100],
            ['DANMARK', 'DK', 1.2500],
            ['PORTUGAL', 'PT', 1.2300],
            ['UKRAINE', 'UA', 1.2000],
            ['FINLAND', 'FI', 1.2400],
            ['GREECE', 'GR', 1.2400],
            ['CROATIA', 'HR', 1.2500],
            ['SERBIA', 'RS', 1.2000],
            ['ITALIA', 'IT', 1.2200],
            ['SLOVENIA', 'SI', 1.2200],
            ['POLAND', 'PL', 1.2300],
            ['LITHUANIA', 'LT', 1.2100],
            ['HUNGARY', 'HU', 1.2700],
            ['LATVIA', 'LV', 1.2100],
            ['SLOVAKIA', 'SK', 1.2000],
            ['ESTONIA', 'EE', 1.2000],
            ['ROMANIA', 'RO', 1.1900],
            ['SWEDEN', 'SE', 1.2500],
            ['NORWAY', 'NO', 1.2500],
            ['CZECH-REPUBLIC', 'CZ', 1.2100],
            ['IRELAND', 'IE', 1.2300],
            ['ANDORRA', 'AD', 1.0450],
        ];

        foreach ($countries as $country) {
            $instance = new Country();
            $instance->setName($country[0]);
            $instance->setIsoCode($country[1]);
            $instance->setVatRate($country[2]);
            $manager->persist($instance);

            $this->addReference(self::getReferenceName($instance->getIsoCode()), $instance);
        }

        $manager->flush();
    }

    public static function getReferenceName(string $key): string
    {
        return sprintf('%s-%s', Country::class, $key);
    }
}
