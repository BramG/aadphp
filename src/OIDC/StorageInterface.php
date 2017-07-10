<?php
/**
 * Copyright (c) 2016 Micorosft Corporation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author James McQuillan <james.mcquillan@remote-learner.net>
 * @license MIT
 * @copyright (C) 2016 onwards Microsoft Corporation (http://microsoft.com/)
 */

namespace microsoft\aadphp\OIDC;

/**
 * Interface defining storage for OIDC.
 */
interface StorageInterface
{
    /**
     * Store the state and corresponding nonce for an OIDC request.
     *
     * @param string $state The state value.
     * @param string $nonce The nonce value.
     * @param array $stateparams Additional data to be stored with the state.
     * @return bool Success/Failure
     */
    public function store_state($state, $nonce, $stateparams);

    /**
     * Get a stored state record.
     *
     * @param string $state The state to look for,
     * @return array List of additional data and the expected nonce.
     */
    public function get_state($state);

    /**
     * Delete a state/nonce record based on the nonce.
     *
     * @param string $nonce The nonce to look for.
     */
    public function delete_state($nonce);
}
