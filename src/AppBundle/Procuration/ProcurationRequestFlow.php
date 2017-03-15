<?php

namespace AppBundle\Procuration;

use Symfony\Component\HttpFoundation\Session\Session;

class ProcurationRequestFlow
{
    const SESSION_KEY = 'app_procuration_model';

    const STEP_NONE = 0;
    const STEP_VOTE = 1;
    const STEP_PROFILE = 2;
    const STEP_ELECTIONS = 2;

    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function reset()
    {
        $this->session->set(self::SESSION_KEY, new ProcurationRequestCommand());
    }

    public function getCurrentModel(): ProcurationRequestCommand
    {
        return $this->session->get(self::SESSION_KEY, new ProcurationRequestCommand());
    }

    public function save(ProcurationRequestCommand $command)
    {
        $this->session->set(self::SESSION_KEY, $command);
    }
}
