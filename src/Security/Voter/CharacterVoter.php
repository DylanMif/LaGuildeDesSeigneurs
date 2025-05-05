<?php

namespace App\Security\Voter;

use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Character;

final class CharacterVoter extends Voter
{
    public const CHARACTER_DISPLAY = 'characterDisplay';
    public const CHARACTER_CREATE = 'characterCreate';
    public const CHARACTER_INDEX = 'characterIndex';
    public const CHARACTER_UPDATE = 'characterUpdate';
    public const CHARACTER_DELETE = 'characterDelete';

    private const ATTRIBUTES = array(
        self::CHARACTER_DISPLAY,
        self::CHARACTER_CREATE,
        self::CHARACTER_INDEX,
        self::CHARACTER_UPDATE,
        self::CHARACTER_DELETE,
    );

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (null !== $subject) {
            return $subject instanceof Character && in_array($attribute, self::ATTRIBUTES);
        }
        return in_array($attribute, self::ATTRIBUTES);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::CHARACTER_DISPLAY:
            case self::CHARACTER_INDEX:
                return $this->canDisplay($token, $subject);
            case self::CHARACTER_CREATE:
                return $this->canCreate($token, $subject);
            case self::CHARACTER_UPDATE:
                return $this->canUpdate($token, $subject);
            case self::CHARACTER_DELETE:
                return $this->canDelete($token, $subject);
        }

        throw new LogicException('Invalid attribute: ' . $attribute);
    }

    private function canDisplay($token, $subject) {
        return true;
    }

    private function canCreate($token, $subject) {
        return true;
    }

    private function canUpdate($token, $subject) {
        return true;
    }

    private function canDelete($token, $subject) {
        return true;
    }
}
