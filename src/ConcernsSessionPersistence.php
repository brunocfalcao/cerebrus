<?php

namespace Brunocfalcao\Cerebrus;

/**
 * This trait manages a session persistence. What this mean?
 * - Uses the property $this->prefix, should be instanciated on
 *   the class constructor with the prefix to be used.
 *   e.g.: $this->prefix = 'eduka:application:user';.
 */
trait ConcernsSessionPersistence
{
    /**
     * The session prefix (without the session id suffix).
     *
     * @var string
     */
    protected $prefix;

    /**
     * In case the session computation returns null, should we still
     * add a session key with a null value or not?
     *
     * @var bool
     */
    protected $allowNulls = false;

    /**
     * Used with the method $this->alwaysRefreshIf().
     *
     * @var bool
     */
    protected $forceRefresh = false;

    /**
     * Refreshes the session key, in case the session is invalid.
     *
     * @param  callable  $callable Function to be called in case session in
     *                            invalid. The function return value will be
     *                            stored into this session key.
     * @param  bool  $invalidate If true, invalidates all other session
     *                          prefixes (that has other session ids).
     * @return void
     */
    public function getOr(callable $callable, bool $invalidate = true)
    {
        /**
         * Verify if we have a valid session for this prefix.
         */
        $session = new Cerebrus();

        // Remove all prefixes except the one that has this session id.
        if ($invalidate) {
            $fullKey = $this->key();
            foreach ($session->all() as $key => $value) {
                if (str_starts_with($key, $this->prefix) && $key != $fullKey) {
                    info('[ConcernsSessionPersistence] - Unsetting "'. $this->key());
                    $session->unset($key);
                }
            }
        }

        // Do we already have a session key?
        if ($session->has($this->key()) || $this->forceRefresh) {
            info('[ConcernsSessionPersistence] - Returning "'. $this->key() . '" key');
            return $session->get($this->key());
        }

        // Compute and store a session key value (if allowed).
        $result = $callable();

        // Add callable result to session if it's not null or if it's null
        // the only allowed if allowNulls = true (default = false).
        if (($this->isEmpty($result) && $this->allowNulls) ||
            ! $this->isEmpty($result)) {
                info('[ConcernsSessionPersistence] - Adding "'. $this->key() . '" with value "' . json_encode($result) . '"');
                $session->set($this->key(), $result);

                return $result;
        }
    }

    /**
     * Forces a session refresh given a callable result.
     * Must be called BEFORE the getOr() method on the target class.
     *
     * @param  callable  $function
     * @return $this
     */
    public function forceRefreshIf(callable $function)
    {
        if ($function() == true) {
            $this->forceRefresh = true;
        } else {
            $this->forceRefresh = false;
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
    public function session()
    {
        return (new Cerebrus())->get($this->key());
    }

    /**
     * If true, will allow session keys with null values.
     *
     * @param  bool  $allow
     * @return $this
     */
    public function allowNulls(bool $allow = true)
    {
        $this->allowNulls = $allow;

        return $this;
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
        if (is_null($this->prefix)) {
            throw new \Exception('Cerebrus session prefix cannot be null');
        }
        return $this->prefix.':'.(new Cerebrus())->getId();
    }

    protected function isEmpty($value)
    {
        if (is_object($value)) {
            $value = (array) $value;
        }

        return empty($value);
    }
}
