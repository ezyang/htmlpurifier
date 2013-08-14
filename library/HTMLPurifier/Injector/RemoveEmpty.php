<?php

class HTMLPurifier_Injector_RemoveEmpty extends HTMLPurifier_Injector
{
    /**
     * @type HTMLPurifier_Context
     */
    private $context;

    /**
     * @type HTMLPurifier_Config
     */
    private $config;

    /**
     * @type HTMLPurifier_AttrValidator
     */
    private $attrValidator;

    /**
     * @type bool
     */
    private $removeNbsp;

    /**
     * @type bool
     */
    private $removeNbspExceptions;

    /**
     * @type array
     * TODO: make me configurable
     */
    private $_exclude = array('colgroup' => 1, 'th' => 1, 'td' => 1, 'iframe' => 1);

    /**
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return void
     */
    public function prepare($config, $context)
    {
        parent::prepare($config, $context);
        $this->config = $config;
        $this->context = $context;
        $this->removeNbsp = $config->get('AutoFormat.RemoveEmpty.RemoveNbsp');
        $this->removeNbspExceptions = $config->get('AutoFormat.RemoveEmpty.RemoveNbsp.Exceptions');
        $this->attrValidator = new HTMLPurifier_AttrValidator();
    }

    /**
     * @param HTMLPurifier_Token $token
     */
    public function handleElement(&$token)
    {
        if (!$token instanceof HTMLPurifier_Token_Start) {
            return;
        }
        $next = false;
        for ($i = $this->inputIndex + 1, $c = count($this->inputTokens); $i < $c; $i++) {
            $next = $this->inputTokens[$i];
            if ($next instanceof HTMLPurifier_Token_Text) {
                if ($next->is_whitespace) {
                    continue;
                }
                if ($this->removeNbsp && !isset($this->removeNbspExceptions[$token->name])) {
                    $plain = str_replace("\xC2\xA0", "", $next->data);
                    $isWsOrNbsp = $plain === '' || ctype_space($plain);
                    if ($isWsOrNbsp) {
                        continue;
                    }
                }
            }
            break;
        }
        if (!$next || ($next instanceof HTMLPurifier_Token_End && $next->name == $token->name)) {
            if (isset($this->_exclude[$token->name])) {
                return;
            }
            $this->attrValidator->validateToken($token, $this->config, $this->context);
            $token->armor['ValidateAttributes'] = true;
            if (isset($token->attr['id']) || isset($token->attr['name'])) {
                return;
            }
            $token = $i - $this->inputIndex + 1;
            for ($b = $this->inputIndex - 1; $b > 0; $b--) {
                $prev = $this->inputTokens[$b];
                if ($prev instanceof HTMLPurifier_Token_Text && $prev->is_whitespace) {
                    continue;
                }
                break;
            }
            // This is safe because we removed the token that triggered this.
            $this->rewind($b - 1);
            return;
        }
    }
}

// vim: et sw=4 sts=4
