<?php
/**
 * User name validator
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Liu Chuang <liuchuang@eefocus.com>
 * @since           3.0
 * @package         Module\User
 * @subpackage      Validator
 * @version         $Id$
 */

namespace Module\User\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

/**
 * Validator for username
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Username extends AbstractValidator
{
    const INVALID   = 'userNameInvalid';
    const RESERVED  = 'userNameReserved';
    const TAKEN     = 'userNameTaken';
    const TOO_SHORT = 'stringLengthTooShort';
    const TOO_LONG  = 'stringLengthTooLong';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID   => 'Invalid user name: %formatHint%',
        self::RESERVED  => 'User name is reserved',
        self::TAKEN     => 'User name is already taken',
        self::TOO_SHORT => 'User name is less than %min% characters long',
        self::TOO_LONG  => 'User name is more than %max% characters long'
    );

    protected $messageVariables = array(
        'formatHint' => 'formatHint',
        'max'        => 'max',
        'min'        => 'min',
    );

    protected $formatHint;
    protected $max;
    protected $min;

    protected $formatMessage = array(
        'strict'    => 'Only alphabetic and digits are allowed with leading alphabetic',
        'medium'    => 'Only ASCII characters are allowed',
        'loose'     => 'Multibyte characters are allowed',
    );

    protected $formatPattern = array(
        'strict'    => '/[^a-zA-Z0-9\_\-]/',
        'medium'    => '/[^a-zA-Z0-9\_\-\<\>\,\.\$\%\#\@\!\\\'\"]/',
        'loose'     => '/[\000-\040]/',
    );

    protected $options = array(
        'format'            => 'strict',
        'backlist'          => array(),
        'checkDuplication'  => true,
    );

    /**
     * User name validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        $this->setConfigOption();

        $format = empty($this->options['format']) ? 'strict' : $this->options['format'];
        if (preg_match($this->formatPattern[$format], $value)) {
            $this->formatHint = $this->formatMessage[$format];
            $this->error(static::INVALID);
            return false;
        }
        if (!empty($this->options['backlist'])) {
            $pattern = is_array($this->options['backlist']) ? implode('|', $this->options['backlist']) : $this->options['backlist'];
            if (preg_match('/(' . $pattern . ')/', $value)) {
                $this->error(static::RESERVED);
                return false;
            }
        }
        if ($this->options['max']) {
            if ($this->options['max'] < strlen($value)) {
                $this->max = $this->options['max'];
                $this->error(static::TOO_LONG);
                return false;
            }
        }
        if ($this->options['min']) {
            if ($this->options['min'] > strlen($value)) {
                $this->min = $this->options['min'];
                $this->error(static::TOO_SHORT);
                return false;
            }
        }
        if ($this->options['checkDuplication']) {
            $where = array('identity' => $value);
            if (!empty($context['uid'])) {
                $where['id <> ?'] = $context['uid'];
            }
            //$rowset = Pi::model('account', 'user')->select($where);
            $count = Pi::model('account', 'user')->count($where);
            if ($count) {
                $this->error(static::TAKEN);
                return false;
            }
        }

        return true;
    }

    /**
     * Set username validator according to config
     *
     * @return $this
     */
    public function setConfigOption()
    {
        $this->options = array(
            'min'       => Pi::service('module')->config('uname_min', 'user'),
            'max'       => Pi::service('module')->config('uname_max', 'user'),
            'format'    => Pi::service('module')->config('uname_format', 'user'),
            'backlist'  => Pi::service('module')->config('uname_backlist', 'user'),
            'format'    => Pi::service('module')->config('name_format', 'user'),
            'checkDuplication' => true,
        );

        return $this;
    }
}
