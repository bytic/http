<?php

if (!function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param array|string $key
     * @param mixed $default
     * @return Nip\Request|string|array
     */
    function request($key = null, $default = null)
    {
        $request = Nip\Utility\Container::get('request');
        if (is_null($key)) {
            return $request;
        }
        $value = $request->get($key);

        return $value ? $value : $default;
    }
}
