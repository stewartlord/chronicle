<?php
/**
 * A very basic interface to define the various resolve option 
 * constants. Intended for use by P4_File and P4_Change.
 *
 * @copyright   2011 Perforce Software. All rights reserved.
 * @license     Please see LICENSE.txt in top-level folder of this distribution.
 * @version     <release>/<patch>
 */
interface P4_ResolvableInterface
{
    const RESOLVE_ACCEPT_MERGED     = 'acceptMerged';
    const RESOLVE_ACCEPT_YOURS      = 'acceptYours';
    const RESOLVE_ACCEPT_THEIRS     = 'acceptTheirs';
    const RESOLVE_ACCEPT_SAFE       = 'acceptSafe';
    const RESOLVE_ACCEPT_FORCE      = 'acceptForce';
    const IGNORE_WHITESPACE_CHANGES = 'ignoreWhitespaceChanges';
    const IGNORE_WHITESPACE         = 'ignoreWhitespace';
    const IGNORE_LINE_ENDINGS       = 'ingoreLineEndings';
}