<?php

class HTMLPurifier_Injector_MathMLSpaceNormalize extends HTMLPurifier_Injector
{

    /**
     * Elements to apply handleText to.
     * These are those that accept #PCDATA except <cs> and <cbytes>.
     * @type array
     */
    protected $tags = array('mi', 'mn', 'mo', 'ms', 'mtext', 'ci', 'cn', 'csymbol', 'annotation');

    /**
     * @param HTMLPurifier_Token $token
     */
    public function handleText(&$token)
    {

        // No parent tag => return to avoid error on following line
        if (count($this->currentNesting) == 0) {
            return;
        }

        // Get the parent tag
        $parent_token = $this->currentNesting[count($this->currentNesting) - 1];

        // If we're not in a "token element" (specified in $tags above), return
        if ($parent_token === null || !in_array($parent_token->name, $this->tags)) {
            return;
        }

        // Replace as per the MathML specification, section 2.1.7
        $token->data = preg_replace(
            '/[ \t\n\r]+/',
            ' ',
            trim($token->data) // Using trim($token->data, ' \t\n\r') trims t,n,r
        );
    }

}
