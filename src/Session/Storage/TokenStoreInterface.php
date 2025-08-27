<?php

namespace Streeboga\Genesis\Session\Storage;

interface TokenStoreInterface
{
    /** Save session data for a given session id */
    public function save(string $sessionId, array $data): void;

    /** Load session data or null if not found */
    public function load(string $sessionId): ?array;

    /** Delete session data */
    public function delete(string $sessionId): void;

    /** List saved session ids (optional) */
    public function listSessionIds(): array;
}


