<?php

namespace Brunocfalcao\Cerebrus;

trait ConcernsSessionPersistence
{
    /**
     * The session prefix (without the session id suffix). Normally defined
     * once on the class constructor, or directly in the attribute assignment.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Sets or gets the current session key (or passed key). You can pass
     * another key prefix in case you are using this method on the same class
     * but for a different prefix than the one assigned via the $this->prefix
     * attribute.
     *
     * @param  callable    $callable The returned session value callable.
     *
     * @return this
     */
    public function persist(callable $callable)
    {
        $this->checkRequirements();

        $session = new Cerebrus();

        // Do we already have a session key? -- Just return it.
        if ($session->has($this->key())) {
            return $this;
        }

        // Compute callable and store it in session.
        $session->set($this->key(), $this->compute($callable));

        return $this;
    }

    /**
     * Returns the possible session key for this session instance.
     *
     * @return mixed
     */
    public function obtain()
    {
        $this->checkRequirements();

        return $this->session();
    }

    /**
     * Overwrites a session key, or writes for the first time.
     *
     * @param  callable $callable The returned session value callable.
     *
     * @return this
     */
    public function overwrite(callable $callable)
    {
        $this->checkRequirements();

        // Compute callable and store/overwrite it in session.
        (new Cerebrus())->set($this->key(), $this->compute($callable));

        return $this;
    }

    public function invalidateIf(callable $callable, $invalidateEmptys = true)
    {
        $this->checkRequirements();

        $session = new Cerebrus();

        if ($callable() === true) {
            $session->unset($this->key());
        }

        return $this;
    }

    /**
     * Sets the session prefix to be used on the next calls.
     *
     * @param  string  $prefix Session prefix
     * @return $this
     */
    public function withPrefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Returns the session value in case the session is valid. If not, then
     * assesses the session and returns the value.
     *
     * @return mixed
     */
    protected function session()
    {
        $this->checkRequirements();

        return (new Cerebrus())->get($this->key());
    }

    public function sessionId()
    {
        return (new Cerebrus())->getId();
    }

    /**
     * Returns the computed key given a prefix.
     *
     * @return string
     */
    protected function key()
    {
        $this->checkRequirements();

        return $this->prefix.':'.(new Cerebrus())->getId();
    }

    /**
     * Computes a callable (normally used for the persist() and overwrite()).
     *
     * @param  callable $callable
     *
     * @return mixed
     */
    protected function compute(callable $callable)
    {
        return $callable();
    }

    protected function checkRequirements()
    {
        if (blank($this->prefix)) {
            throw new \Exception("No key defined for the session persistence instance");
        }
    }
}
