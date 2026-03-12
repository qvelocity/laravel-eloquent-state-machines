<?php


namespace Asantibanez\LaravelEloquentStateMachines\StateMachines;

use Asantibanez\LaravelEloquentStateMachines\Exceptions\TransitionNotAllowedException;
use Asantibanez\LaravelEloquentStateMachines\Models\PendingTransition;
use Asantibanez\LaravelEloquentStateMachines\Models\StateHistory;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Class State
 * @package Asantibanez\LaravelEloquentStateMachines\StateMachines
 * @property string $state
 * @property StateMachine $stateMachine
 */
class State
{
    public $state;
    public $stateMachine;

    public function __construct($state, $stateMachine)
    {
        $this->state = $state;
        $this->stateMachine = $stateMachine;
    }

    public function state()
    {
        return $this->state;
    }

    public function stateMachine()
    {
        return $this->stateMachine;
    }

    public function is($state)
    {
        return $this->state === $state;
    }

    public function isNot($state)
    {
        return !$this->is($state);
    }

    public function was($state)
    {
        return $this->stateMachine->was($state);
    }

    public function timesWas($state)
    {
        return $this->stateMachine->timesWas($state);
    }

    public function whenWas($state)
    {
        return $this->stateMachine->whenWas($state);
    }

    public function snapshotWhen($state)
    {
        return $this->stateMachine->snapshotWhen($state);
    }

    public function snapshotsWhen($state)
    {
        return $this->stateMachine->snapshotsWhen($state);
    }

    public function history()
    {
        return $this->stateMachine->history();
    }

    public function canBe($state)
    {
        return $this->stateMachine->canBe($from = $this->state, $to = $state);
    }

    public function pendingTransitions()
    {
        return $this->stateMachine->pendingTransitions();
    }

    public function hasPendingTransitions()
    {
        return $this->stateMachine->hasPendingTransitions();
    }

    public function transitionTo($state, $customProperties = [], $responsible = null)
    {
        $this->stateMachine->transitionTo(
            $from = $this->state,
            $to = $state,
            $customProperties,
            $responsible
        );
    }

    public function transitionToQuietly($state, $customProperties = [], $responsible = null)
    {
        $this->stateMachine->transitionToQuietly(
            $from = $this->state,
            $to = $state,
            $customProperties,
            $responsible
        );
    }

    /**
     * @param mixed $to
     * @param string|array|null $additionalDetails
     */
    public function transitionIfCanBe($to, $additionalDetails = null) : bool
    {
        if (!$this->canBe($to) && !$this->stateMachine->canBe('*', $to)) {
            return false;
        }

        if ($additionalDetails) {
            $customProperties = is_array($additionalDetails)
                ? $additionalDetails
                : ['Additional Details' => $additionalDetails];
        }

        $this->transitionTo($to, $customProperties ?? []);

        return true;
    }

    /**
     * @param mixed $to
     * @param string|array|null $additionalDetails
     * @throws ValidationException
     */
    public function transitionIfCanBeQuietly($to, $additionalDetails = null) : bool
    {
        if (!$this->canBe($to) && !$this->stateMachine->canBe('*', $to)) {
            return false;
        }

        if ($additionalDetails) {
            $customProperties = is_array($additionalDetails)
                ? $additionalDetails
                : ['Additional Details' => $additionalDetails];
        }

        $this->stateMachine->transitionToQuietly(
            $from = $this->state,
            $to,
            $customProperties ?? []
        );

        return true;
    }

    /**
     * @param $state
     * @param Carbon $when
     * @param array $customProperties
     * @param null $responsible
     * @return null|PendingTransition
     * @throws TransitionNotAllowedException
     */
    public function postponeTransitionTo($state, Carbon $when, $customProperties = [], $responsible = null) : ?PendingTransition
    {
        return $this->stateMachine->postponeTransitionTo(
            $from = $this->state,
            $to = $state,
            $when,
            $customProperties,
            $responsible
        );
    }

    public function latest() : ?StateHistory
    {
        return $this->snapshotWhen($this->state);
    }

    public function getCustomProperty($key)
    {
        return optional($this->latest())->getCustomProperty($key);
    }

    public function responsible()
    {
        return optional($this->latest())->responsible;
    }

    public function allCustomProperties()
    {
        return optional($this->latest())->allCustomProperties();
    }
}
