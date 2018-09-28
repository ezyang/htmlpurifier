<?php

/**
 * MathML 3 specification.
 */
class HTMLPurifier_HTMLModule_Math extends HTMLPurifier_HTMLModule
{
    /**
     * @type string
     */
    public $name = 'Math';

    // Prefix in case MathML is imported
    private $mathml_prefix = 'm';

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {

        /*****************************************************************
         * Meta variables
         * Used in this file to simplify code and help adapt the DTD
         *****************************************************************/

        // The elements inside <math> are not supposed to be outside, so they
        // can't be neither block nor inline:
        $default_display = false;

        // This array will contain all the necessary DTD entities used
        // throughout the MathML DTD, in order to avoid collisions and allow
        // for special characters ('.', '-') in entity names
        $E = array();

        /*****************************************************************
         * DTD code
         * Code from the DTD ported and adapted
         *****************************************************************/
        
        $E['MalignExpression'] = 'maligngroup|malignmark';
        $E['TokenExpression'] = 'mi|mn|mo|mtext|mspace|ms';
        $E['PresentationExpression'] =
            $E['TokenExpression'] .
            '|' . $E['MalignExpression'] .
            '|mrow|mfrac|msqrt|mroot|mstyle|merror|mpadded|mphantom|mfenced' .
            '|menclose|msub|msup|msubsup|munder|mover|munderover' .
            '|mmultiscripts|mtable|mstack|mlongdiv|maction';

        $this->addElement(
            'cn',
            $default_display,
            'Custom: #PCDATA|mglyph|sep|' . $E['PresentationExpression'],
            array(),
            array()
        );

        $this->addElement(
            'ci',
            $default_display,
            'Custom: #PCDATA|mglyph|' . $E['PresentationExpression'],
            array(),
            array()
        );

        $this->addElement(
            'csymbol',
            $default_display,
            'Custom: #PCDATA|mglyph|' . $E['PresentationExpression'],
            array(),
            array()
        );

        $E['SymbolName'] = '#PCDATA';
        $E['BvarQ'] = '(bvar)*';
        $E['DomainQ'] = '(domainofapplication|condition|(lowlimit,uplimit?))*';
        $E['constant-arith.mmlclass'] = 'exponentiale|imaginaryi|notanumber' .
            '|true|false|pi|eulergamma|infinity';
        $E['constant-set.mmlclass'] = 'integers|reals|rationals' .
            '|naturalnumbers|complexes|primes|emptyset';
        $E['binary-linalg.mmlclass'] = 'vectorproduct|scalarproduct' .
            '|outerproduct';
        $E['nary-linalg.mmlclass'] = 'max|min';
        $E['unary-linalg.mmlclass'] = 'determinant|transpose';
        $E['nary-constructor.mmlclass'] = 'vector|matrix|matrixrow';
        $E['nary-stats.mmlclass'] = 'mean|sdev|variance|median|mode';
        $E['unary-elementary.mmlclass'] = 'sin|cos|tan|sec|csc|cot|sinh|cosh|tanh|sech' .
            '|csch|coth|arcsin|arccos|arctan|arccosh|arccot|arccoth|arccsc' .
            '|arccsch|arcsec|arcsech|arcsinh|arctanh';
        $E['limit.mmlclass'] = 'limit';
        $E['product.mmlclass'] = 'product';
        $E['sum.mmlclass'] = 'sum';
        $E['unary-set.mmlclass'] = 'card';
        $E['nary-set-reln.mmlclass'] = 'subset|prsubset';
        $E['binary-set.mmlclass'] = 'in|notin|notsubset|notprsubset|setdiff';
        $E['nary-set.mmlclass'] = 'union|intersect|cartesianproduct';
        $E['nary-setlist-constructor.mmlclass'] = 'set|list';
        $E['unary-veccalc.mmlclass'] = 'divergence|grad|curl|laplacian';
        $E['partialdiff.mmlclass'] = 'partialdiff';
        $E['Differential-Operator.mmlclass'] = 'diff';
        $E['int.mmlclass'] = 'int';
        $E['binary-reln.mmlclass'] = 'neq|approx|factorof|tendsto';
        $E['nary-reln.mmlclass'] = 'eq|gt|lt|geq|leq';
        $E['quantifier.mmlclass'] = 'forall|exists';
        $E['binary-logical.mmlclass'] = 'implies|equivalent';
        $E['unary-logical.mmlclass'] = 'not';
        $E['nary-logical.mmlclass'] = 'and|or|xor';
        $E['nary-arith.mmlclass'] = 'plus|times|gcd|lcm';
        $E['nary-minmax.mmlclass'] = 'max|min';
        $E['unary-arith.mmlclass'] = 'factorial|abs|conjugate|arg|real' .
            '|imaginary|floor|ceiling|exp';
        $E['binary-arith.mmlclass'] = 'quotient|divide|minus|power|rem|root';
        $E['nary-functional.mmlclass'] = 'compose';
        $E['lambda.mmlclass'] = 'lambda';
        $E['unary-functional.mmlclass'] = 'inverse|ident|domain|codomain' .
            '|image|ln|log|moment';
        $E['interval.mmlclass'] = 'interval';
        $E['DeprecatedContExp'] = 'reln|fn|declare';
        $E['Qualifier'] = '(' . $E['DomainQ'] . ')|degree|momentabout|logbase';
        $E['ContExp'] = 'piecewise|' .
            $E['DeprecatedContExp'] . 
            '|' . $E['interval.mmlclass'] .
            '|' . $E['unary-functional.mmlclass'] .
            '|' . $E['lambda.mmlclass'] .
            '|' . $E['nary-functional.mmlclass'] .
            '|' . $E['binary-arith.mmlclass'] .
            '|' . $E['nary-minmax.mmlclass'] .
            '|' . $E['nary-arith.mmlclass'] .
            '|' . $E['nary-logical.mmlclass'] .
            '|' . $E['unary-logical.mmlclass'] .
            '|' . $E['binary-logical.mmlclass'] .
            '|' . $E['quantifier.mmlclass'] .
            '|' . $E['nary-reln.mmlclass'] .
            '|' . $E['binary-reln.mmlclass'] .
            '|' . $E['int.mmlclass'] .
            '|' . $E['Differential-Operator.mmlclass'] .
            '|' . $E['partialdiff.mmlclass'] .
            '|' . $E['unary-veccalc.mmlclass'] .
            '|' . $E['nary-setlist-constructor.mmlclass'] .
            '|' . $E['nary-set.mmlclass'] .
            '|' . $E['binary-set.mmlclass'] .
            '|' . $E['nary-set-reln.mmlclass'] .
            '|' . $E['unary-set.mmlclass'] .
            '|' . $E['sum.mmlclass'] .
            '|' . $E['product.mmlclass'] .
            '|' . $E['limit.mmlclass'] .
            '|' . $E['unary-elementary.mmlclass'] .
            '|' . $E['nary-stats.mmlclass'] .
            '|' . $E['nary-constructor.mmlclass'] .
            '|' . $E['unary-linalg.mmlclass'] .
            '|' . $E['nary-linalg.mmlclass'] .
            '|' . $E['binary-linalg.mmlclass'] .
            '|' . $E['constant-set.mmlclass'] .
            '|' . $E['constant-arith.mmlclass'] .
            '|semantics|cn|ci|csymbol|apply|bind|share|cerror|cbytes|cs';

        $E['apply.content'] = '(' . $E['ContExp'] . '),(' . $E['BvarQ'] .
        '),(' . $E['Qualifier'] . ')*,(' . $E['ContExp'] . ')*';

        $this->addElement(
            'apply',
            $default_display,
            'Custom: ' . $E['apply.content'],
            array(),
            array()
        );

        $this->addElement(
            'bind',
            $default_display,
            'Custom: ' . $E['apply.content'],
            array(),
            array()
        );

        $this->addElement(
            'share',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'cerror',
            $default_display,
            'Custom: (csymbol,(' . $E['ContExp'] . ')*)',
            array(),
            array()
        );

        $this->addElement(
            'cbytes',
            $default_display,
            'Custom: (#PCDATA)',
            array(),
            array()
        );

        $this->addElement(
            'cs',
            $default_display,
            'Custom: (#PCDATA)',
            array(),
            array()
        );

        $this->addElement(
            'bvar',
            $default_display,
            'Custom: ((degree,(ci|semantics))|((ci|semantics),(degree)?))',
            array(),
            array()
        );

        $this->addElement(
            'sep',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'domainofapplication',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'condition',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'uplimit',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'lowlimit',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'degree',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'momentabout',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'logbase',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'piecewise',
            $default_display,
            'Custom: (piece|otherwise)',
            array(),
            array()
        );

        $this->addElement(
            'piece',
            $default_display,
            'Custom: ((' . $E['ContExp'] . '),(' . $E['ContExp'] . '))',
            array(),
            array()
        );

        $this->addElement(
            'otherwise',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'reln',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'fn',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'declare',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')+',
            array(),
            array()
        );

        $this->addElement(
            'interval',
            $default_display,
            'Custom: ((' . $E['ContExp'] . '),(' . $E['ContExp'] . '))',
            array(),
            array()
        );

        $this->addElement(
            'inverse',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'ident',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'domain',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'codomain',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'image',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'ln',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'log',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'moment',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'lambda',
            $default_display,
            'Custom: ((' . $E['BvarQ'] . '),(' . $E['DomainQ'] . '),(' .
                $E['ContExp'] . '))',
            array(),
            array()
        );

        $this->addElement(
            'compose',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'quotient',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'divide',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'minus',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'power',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'rem',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'root',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'factorial',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'abs',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'conjugate',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arg',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'real',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'imaginary',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'floor',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'ceiling',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'exp',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'max',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'min',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'plus',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'times',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'gcd',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'lcm',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'and',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'or',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'xor',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'not',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'implies',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'equivalent',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'forall',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'exists',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'eq',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'gt',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'lt',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'geq',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'leq',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'neq',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'approx',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'factorof',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'tendsto',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'int',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'diff',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'partialdiff',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'divergence',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'grad',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'curl',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'laplacian',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'set',
            $default_display,
            'Custom: ((' . $E['BvarQ'] . ')*,(' . $E['DomainQ'] . ')*,(' .
                $E['ContExp'] . ')*)',
            array(),
            array()
        );

        $this->addElement(
            'list',
            $default_display,
            'Custom: ((' . $E['BvarQ'] . ')*,(' . $E['DomainQ'] . ')*,(' .
                $E['ContExp'] . ')*)',
            array(),
            array()
        );

        $this->addElement(
            'union',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'intersect',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'cartesianproduct',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'in',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'notin',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'notsubset',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'notprsubset',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'setdiff',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'subset',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'prsubset',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'card',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'sum',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'product',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'limit',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'sin',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'cos',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'tan',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'sec',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'csc',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'cot',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'sinh',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'cosh',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'tanh',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'sech',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'csch',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'coth',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arcsin',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arccos',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arctan',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arccosh',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arccot',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arccoth',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arccsc',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arccsch',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arcsec',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arcsech',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arcsinh',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'arctanh',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'mean',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'sdev',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'variance',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'median',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'mode',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'vector',
            $default_display,
            'Custom: ((' . $E['BvarQ'] . '),(' . $E['DomainQ'] . '),(' .
                $E['ContExp'] . ')*)',
            array(),
            array()
        );

        $this->addElement(
            'matrix',
            $default_display,
            'Custom: ((' . $E['BvarQ'] . '),(' . $E['DomainQ'] . '),(' .
                $E['ContExp'] . ')*)',
            array(),
            array()
        );

        $this->addElement(
            'matrixrow',
            $default_display,
            'Custom: ((' . $E['BvarQ'] . '),(' . $E['DomainQ'] . '),(' .
                $E['ContExp'] . ')*)',
            array(),
            array()
        );

        $this->addElement(
            'determinant',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'transpose',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'selector',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'vectorproduct',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'scalarproduct',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'outerproduct',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'integers',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'reals',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'rationals',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'naturalnumbers',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'complexes',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'primes',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'emptyset',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'exponentiale',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'imaginaryi',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'notanumber',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'true',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'false',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'pi',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'eulergamma',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'infinity',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $E['MathExpression'] = $E['ContExp'] .
            '|' . $E['PresentationExpression'];
        $E['ImpliedMrow'] = '(' . $E['MathExpression'] . ')';
        $E['TableRowExpression'] = 'mtr|mlabeledtr';
        $E['TableCellExpression'] = 'mtd';
        $E['MstackExpression'] = $E['MathExpression'] .
            '|mscarries|msline|msrow|msgroup';
        $E['MsrowExpression'] = $E['MathExpression'] . '|none';
        $E['MultiscriptExpression'] = '(' .
            $E['MathExpression'] . '|none),(' .
            $E['MathExpression'] . '|none)';

        $E['token.content'] = '#PCDATA|mglyph|malignmark';

        $this->addElement(
            'mi',
            $default_display,
            'Custom: (' . $E['token.content'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'mn',
            $default_display,
            'Custom: (' . $E['token.content'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'mo',
            $default_display,
            'Custom: (' . $E['token.content'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'mtext',
            $default_display,
            'Custom: (' . $E['token.content'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'mspace',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'ms',
            $default_display,
            'Custom: (' . $E['token.content'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'mglyph',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'msline',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'none',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'mprescripts',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'malignmark',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'maligngroup',
            $default_display,
            'Empty',
            array(),
            array()
        );

        $this->addElement(
            'mrow',
            $default_display,
            'Custom: (' . $E['MathExpression'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'mfrac',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
                $E['MathExpression'] . '))',
            array(),
            array()
        );

        $this->addElement(
            'msqrt',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'mroot',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
                $E['MathExpression'] . '))',
            array(),
            array()
        );

        $this->addElement(
            'mstyle',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'merror',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'mpadded',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'mphantom',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'mfenced',
            $default_display,
            'Custom: (' . $E['MathExpression'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'menclose',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'msub',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
                $E['MathExpression'] . '))',
            array(),
            array()
        );

        $this->addElement(
            'msup',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
                $E['MathExpression'] . '))',
            array(),
            array()
        );

        $this->addElement(
            'msubsup',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
                $E['MathExpression'] . '),(' .
                $E['MathExpression'] . '))',
            array(),
            array()
        );

        $this->addElement(
            'munder',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
                $E['MathExpression'] . '))',
            array(),
            array()
        );

        $this->addElement(
            'mover',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
                $E['MathExpression'] . '))',
            array(),
            array()
        );

        $this->addElement(
            'munderover',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
                $E['MathExpression'] . '),(' .
                $E['MathExpression'] . '))',
            array(),
            array()
        );

        $this->addElement(
            'mmultiscripts',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
                $E['MultiscriptExpression'] . ')*,(mprescripts,(' .
                $E['MultiscriptExpression'] . ')*)?)',
            array(),
            array()
        );

        $this->addElement(
            'mtable',
            $default_display,
            'Custom: (' . $E['TableRowExpression'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'mlabeledtr',
            $default_display,
            'Custom: (' . $E['TableCellExpression'] . ')+',
            array(),
            array()
        );

        $this->addElement(
            'mtr',
            $default_display,
            'Custom: (' . $E['TableCellExpression'] . ')+',
            array(),
            array()
        );

        $this->addElement(
            'mtd',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'mstack',
            $default_display,
            'Custom: (' . $E['MstackExpression'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'mlongdiv',
            $default_display,
            'Custom: ((' . $E['MstackExpression'] . '),(' .
                $E['MstackExpression'] . '),(' . $E['MstackExpression'] . ')+)',
            array(),
            array()
        );

        $this->addElement(
            'msgroup',
            $default_display,
            'Custom: (' . $E['MstackExpression'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'msrow',
            $default_display,
            'Custom: (' . $E['MsrowExpression'] . ')',
            array(),
            array()
        );

        $this->addElement(
            'mscarries',
            $default_display,
            'Custom: (' . $E['MsrowExpression'] . '|mscarry)*',
            array(),
            array()
        );

        $this->addElement(
            'mscarry',
            $default_display,
            'Custom: (' . $E['MsrowExpression'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'maction',
            $default_display,
            'Custom: (' . $E['MathExpression'] . ')+',
            array(),
            array()
        );

        $this->addElement(
            'math',
            // The specification allows math to be either inline or block
            // according to the display parameter or infer it from context.
            // We set it to Flow so that it can be inside both elements that
            // allow inline, and elements that allow block
            'Flow',
            'Custom: (' . $E['MathExpression'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'annotation',
            $default_display,
            'Custom: (#PCDATA)',
            array(),
            array()
        );

        $this->addElement(
            'annotation-xml',
            $default_display,
            'Custom: (' . $E['MathExpression'] . ')*',
            array(),
            array()
        );

        $this->addElement(
            'semantics',
            $default_display,
            'Custom: (' . $E['MathExpression'] .
                '),(annotation|annotation-xml)*)',
            array(),
            array()
        );

    }

}