<?php

namespace Laminas\View\Helper;

/**
 * Helper for setting and retrieving script elements for inclusion in HTML body
 * section
 */
class InlineScript extends HeadScript
{
    /**
     * Return InlineScript object
     *
     * Returns InlineScript helper object; optionally, allows specifying a
     * script or script file to include.
     *
     * @param  string $mode      Script or file
     * @param  string $spec      Script/url
     * @param  string $placement Append, prepend, or set
     * @param  array  $attrs     Array of script attributes
     * @param  string $type      Script type and/or array of script attributes
     * @return InlineScript
     */
    public function __invoke(
        $mode = self::FILE,
        $spec = null,
        $placement = 'APPEND',
        array $attrs = [],
        $type = self::DEFAULT_SCRIPT_TYPE
    ) {
        return parent::__invoke($mode, $spec, $placement, $attrs, $type);
    }
}
