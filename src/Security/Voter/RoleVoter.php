<?php

namespace App\Security\Voter;

use App\Entity\Meeting;
use App\Entity\Person;
use App\Entity\Project\Project;
use App\Handler\Role\RoleHandler;
use App\Service\Person\PersonService;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RoleVoter extends Voter
{
    /** @var array */
    private $roles = [];

    /**
     * @var PersonService
     */
    private $personService;

    /**
     * RoleVoter constructor.
     * @param RoleHandler $roleHandler
     * @param PersonService $personService
     */
    public function __construct(RoleHandler $roleHandler, PersonService $personService)
    {
        if ('cli' === php_sapi_name()) {
            return;
        }

        $this->roles = $roleHandler->getNames();
        $this->personService = $personService;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, $this->roles);
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /**
         * @var Person
         */
        $person = $token->getUser();

        if (!$person instanceof Person) {
            return false;
        }

        return $this->voteOnAttributeForUser($attribute, $subject, $person);
    }

    /**
     * @param string $attribute
     * @param mixed  $subject
     * @param Person $person
     *
     * @return bool
     */
    public function voteOnAttributeForUser($attribute, $subject, Person $person): bool
    {
        if ($person->isSuperAdmin()) {
            return true;
        }

        if (
            !(
                $this->personService->hasRole($person, $attribute)
                || $this->personService->hasRoleInTypes($person, $attribute)
                || $this->personService->hasRoleAssociate($person, $attribute)
            )
        ) {
            return false;
        }

        if (!$subject) {
            return true;
        }

        switch ($attribute) {
            case 'PROJECT_VIEW':
                /* @var Project $subject */
                return $this->personService->canAccessProject($person, $subject);
            case 'TICKET_VIEW':
                /* @var Project $subject */
                return $this->personService->canAccessTicket($person, $subject);
            case 'MEETING_VIEW':
                /* @var Meeting $subject */
                return $this->personService->canAccessMeeting($person, $subject);
        }

        throw new LogicException("Role person `{$attribute}` n'est pas géré");
    }
}
