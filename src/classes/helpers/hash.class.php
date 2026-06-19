<?php

namespace Obadiah\Helpers;

use Obadiah\App;
use Obadiah\AddressBook\Person;
use Obadiah\Rota\Combined_Service;

App::check();

class Hash
{
    /**
     * Generate a hash of an events query.
     *
     * @param string $query             URL-encoded query (e.g. using http_build_query()).
     * @return string                   MD5 hash.
     */
    public static function events_query(string $query): string
    {
        return md5($query);
    }

    /**
     * Generate a hash of a person.
     *
     * @param Person $person            Person object.
     * @return string                   MD5 hash.
     */
    public static function person(Person $person): string
    {
        return md5(sprintf("%s-%s",$person->id, $person->get_full_name(false)));
    }

    /**
     * Generate a hash of a service.
     *
     * @param Combined_Service $service Service object.
     * @return string                   MD5 hash.
     */
    public static function service(Combined_Service $service): string
    {
        return md5(Serialise::store($service));
    }
}
