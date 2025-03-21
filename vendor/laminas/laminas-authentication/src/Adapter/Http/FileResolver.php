<?php

declare(strict_types=1);

namespace Laminas\Authentication\Adapter\Http;

use Laminas\Stdlib\ErrorHandler;

use function ctype_print;
use function fclose;
use function fgetcsv;
use function fopen;
use function is_readable;
use function strpos;

use const E_WARNING;

/**
 * HTTP Authentication File Resolver
 */
class FileResolver implements ResolverInterface
{
    /**
     * Path to credentials file
     *
     * @var string|null
     */
    protected $file;

    /**
     * Constructor
     *
     * @param  string $path Complete filename where the credentials are stored
     */
    public function __construct($path = '')
    {
        if (! empty($path)) {
            $this->setFile($path);
        }
    }

    /**
     * Set the path to the credentials file
     *
     * @param  string $path
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException If path is not readable.
     */
    public function setFile($path)
    {
        if (empty($path) || ! is_readable($path)) {
            throw new Exception\InvalidArgumentException('Path not readable: ' . $path);
        }
        $this->file = $path;

        return $this;
    }

    /**
     * Returns the path to the credentials file
     *
     * @return string|null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Resolve credentials
     *
     * Only the first matching username/realm combination in the file is
     * returned. If the file contains credentials for Digest authentication,
     * the returned string is the password hash, or h(a1) from RFC 2617. The
     * returned string is the plain-text password for Basic authentication.
     *
     * The expected format of the file is:
     *   username:realm:sharedSecret
     *
     * That is, each line consists of the user's username, the applicable
     * authentication realm, and the password or hash, each delimited by
     * colons.
     *
     * @param  string $username Username
     * @param  string $realm    Authentication Realm
     * @param string|null $password
     * @return string|false User's shared secret, if the user is found in the
     *         realm, false otherwise.
     * @throws Exception\ExceptionInterface
     */
    public function resolve($username, $realm, $password = null)
    {
        if (empty($username)) {
            throw new Exception\InvalidArgumentException('Username is required');
        } elseif (! ctype_print($username) || strpos($username, ':') !== false) {
            throw new Exception\InvalidArgumentException(
                'Username must consist only of printable characters, excluding the colon'
            );
        }
        if (empty($realm)) {
            throw new Exception\InvalidArgumentException('Realm is required');
        } elseif (! ctype_print($realm) || strpos($realm, ':') !== false) {
            throw new Exception\InvalidArgumentException(
                'Realm must consist only of printable characters, excluding the colon.'
            );
        }

        // Open file, read through looking for matching credentials
        ErrorHandler::start(E_WARNING);
        $fp    = fopen($this->file, 'r');
        $error = ErrorHandler::stop();
        if (! $fp) {
            throw new Exception\RuntimeException('Unable to open password file: ' . $this->file, 0, $error);
        }

        // No real validation is done on the contents of the password file. The
        // assumption is that we trust the administrators to keep it secure.
        while (($line = fgetcsv($fp, 512, ':', '"', escape: "\\")) !== false) {
            if ($line[0] === $username && $line[1] === $realm) {
                $password = $line[2];
                fclose($fp);
                return $password;
            }
        }

        fclose($fp);
        return false;
    }
}
