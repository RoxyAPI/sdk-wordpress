<?php
// This file is generated. Do not modify it manually.
return array(
	'angel-number' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/angel-number',
		'title' => 'Angel Number',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'Angel number meanings and pattern analysis. Powered by RoxyAPI.',
		'keywords' => array(
			'angel number',
			'spiritual',
			'guidance',
			'roxyapi'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			
		),
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js',
		'usesContext' => array(
			'roxyapi/sign',
			'roxyapi/birthDate',
			'roxyapi/lat',
			'roxyapi/lon'
		)
	),
	'astrology-wrapper' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/astrology-wrapper',
		'title' => 'Astrology Section',
		'category' => 'roxyapi',
		'icon' => 'screenoptions',
		'description' => 'Wrapper block that shares a default zodiac sign and birth date with every RoxyAPI block inside it via block context.',
		'keywords' => array(
			'astrology',
			'zodiac',
			'section',
			'roxyapi'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'defaultSign' => array(
				'type' => 'string',
				'default' => 'aries'
			),
			'defaultBirthDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'defaultLat' => array(
				'type' => 'number'
			),
			'defaultLon' => array(
				'type' => 'number'
			)
		),
		'providesContext' => array(
			'roxyapi/sign' => 'defaultSign',
			'roxyapi/birthDate' => 'defaultBirthDate',
			'roxyapi/lat' => 'defaultLat',
			'roxyapi/lon' => 'defaultLon'
		),
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js'
	),
	'biorhythm' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/biorhythm',
		'title' => 'Biorhythm',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Physical, emotional, intellectual, and intuitive cycles. Powered by RoxyAPI.',
		'keywords' => array(
			'biorhythm',
			'cycles',
			'wellness',
			'roxyapi'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			
		),
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js',
		'usesContext' => array(
			'roxyapi/sign',
			'roxyapi/birthDate',
			'roxyapi/lat',
			'roxyapi/lon'
		)
	),
	'crystal' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/crystal',
		'title' => 'Crystal',
		'category' => 'roxyapi',
		'icon' => 'heart',
		'description' => 'Crystal properties, zodiac pairings, and healing guidance. Powered by RoxyAPI.',
		'keywords' => array(
			'crystal',
			'healing',
			'chakra',
			'roxyapi'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			
		),
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js',
		'usesContext' => array(
			'roxyapi/sign',
			'roxyapi/birthDate',
			'roxyapi/lat',
			'roxyapi/lon'
		)
	),
	'dreams' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/dreams',
		'title' => 'Dream Symbol',
		'category' => 'roxyapi',
		'icon' => 'lightbulb',
		'description' => 'Dream symbol dictionary with meanings and interpretations. Powered by RoxyAPI.',
		'keywords' => array(
			'dream',
			'interpretation',
			'symbol',
			'roxyapi'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			
		),
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js',
		'usesContext' => array(
			'roxyapi/sign',
			'roxyapi/birthDate',
			'roxyapi/lat',
			'roxyapi/lon'
		)
	),
	'horoscope' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/horoscope',
		'title' => 'Horoscope',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'Daily, weekly, monthly, love, career, and Chinese horoscopes for any zodiac sign. Powered by RoxyAPI.',
		'keywords' => array(
			'horoscope',
			'astrology',
			'zodiac',
			'roxyapi'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			),
			'typography' => array(
				'fontSize' => true
			)
		),
		'attributes' => array(
			'sign' => array(
				'type' => 'string',
				'default' => 'aries'
			),
			'period' => array(
				'type' => 'string',
				'default' => 'daily'
			),
			'type' => array(
				'type' => 'string',
				'default' => 'general'
			),
			'date' => array(
				'type' => 'string',
				'default' => 'today'
			)
		),
		'usesContext' => array(
			'roxyapi/sign',
			'roxyapi/birthDate'
		),
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js',
		'viewStyle' => 'file:./style.css',
		'editorStyle' => 'file:./editor.css'
	),
	'iching' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/iching',
		'title' => 'I Ching',
		'category' => 'roxyapi',
		'icon' => 'book',
		'description' => 'I Ching hexagram cast and interpretation. Powered by RoxyAPI.',
		'keywords' => array(
			'i ching',
			'hexagram',
			'divination',
			'roxyapi'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			
		),
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js',
		'usesContext' => array(
			'roxyapi/sign',
			'roxyapi/birthDate',
			'roxyapi/lat',
			'roxyapi/lon'
		)
	),
	'natal-chart' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/natal-chart',
		'title' => 'Natal Chart',
		'category' => 'roxyapi',
		'icon' => 'admin-users',
		'description' => 'Western birth chart with planets, houses, and aspects. Powered by RoxyAPI.',
		'keywords' => array(
			'birth chart',
			'astrology',
			'natal chart',
			'roxyapi'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			
		),
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js',
		'usesContext' => array(
			'roxyapi/sign',
			'roxyapi/birthDate',
			'roxyapi/lat',
			'roxyapi/lon'
		)
	),
	'numerology' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/numerology',
		'title' => 'Numerology',
		'category' => 'roxyapi',
		'icon' => 'calculator',
		'description' => 'Life path, expression, soul urge, and personal year numbers. Powered by RoxyAPI.',
		'keywords' => array(
			'numerology',
			'life path',
			'numbers',
			'roxyapi'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			
		),
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js',
		'usesContext' => array(
			'roxyapi/sign',
			'roxyapi/birthDate',
			'roxyapi/lat',
			'roxyapi/lon'
		)
	),
	'tarot' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/tarot',
		'title' => 'Tarot',
		'category' => 'roxyapi',
		'icon' => 'tag',
		'description' => 'Tarot card readings: single, three card, Celtic Cross, and more. Powered by RoxyAPI.',
		'keywords' => array(
			'tarot',
			'card reading',
			'spread',
			'roxyapi'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			
		),
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js',
		'usesContext' => array(
			'roxyapi/sign',
			'roxyapi/birthDate',
			'roxyapi/lat',
			'roxyapi/lon'
		)
	),
	'analyze-karmic-lessons' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/analyze-karmic-lessons',
		'title' => 'Analyze Karmic Lessons - Life lessons from missing numbers (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Analyze Karmic Lessons - Life lessons from missing numbers. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'analyze',
			'karmic'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'fullName' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'analyze-number-sequence' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/analyze-number-sequence',
		'title' => 'Analyze Any Number Sequence (Angel Numbers)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Analyze Any Number Sequence. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'angel numbers',
			'roxyapi',
			'analyze',
			'number'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'number' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-ashtakavarga' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-ashtakavarga',
		'title' => 'Get Ashtakavarga (planetary strength) analysis - Ashtakavarga Calculator API (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get Ashtakavarga (planetary strength) analysis - Ashtakavarga Calculator API. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'calculate',
			'ashtakavarga'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-aspects' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-aspects',
		'title' => 'Calculate planetary aspects - Aspect finder for any date and time (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Calculate planetary aspects - Aspect finder for any date and time. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'calculate',
			'aspects'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'planets' => array(
				'type' => 'string',
				'default' => ''
			),
			'aspectTypes' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-bio-compatibility' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-bio-compatibility',
		'title' => 'Calculate compatibility - Biorhythm alignment between two people (Biorhythm)',
		'category' => 'roxyapi',
		'icon' => 'tide',
		'description' => 'Calculate compatibility - Biorhythm alignment between two people. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'biorhythm',
			'roxyapi',
			'calculate',
			'bio'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'person1' => array(
				'type' => 'string',
				'default' => ''
			),
			'person2' => array(
				'type' => 'string',
				'default' => ''
			),
			'targetDate' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-birth-day' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-birth-day',
		'title' => 'Calculate Birth Day number - Special talents from day of birth (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Calculate Birth Day number - Special talents from day of birth. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'calculate',
			'birth'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'day' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-bridge-numbers' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-bridge-numbers',
		'title' => 'Calculate Bridge Numbers - Harmonize different aspects of personality (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Calculate Bridge Numbers - Harmonize different aspects of personality. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'calculate',
			'bridge'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'fullName' => array(
				'type' => 'string',
				'default' => ''
			),
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'day' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-compatibility' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-compatibility',
		'title' => 'Compatibility Score. Relationship compatibility analysis with category breakdown (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Compatibility Score. Relationship compatibility analysis with category breakdown. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'calculate',
			'compatibility'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'person1' => array(
				'type' => 'string',
				'default' => ''
			),
			'person2' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-drishti' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-drishti',
		'title' => 'Get planetary aspects (Drishti) - Mutual aspects between all planets (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get planetary aspects (Drishti) - Mutual aspects between all planets. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'calculate',
			'drishti'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'coordinateSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-expression' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-expression',
		'title' => 'Calculate Expression number - Natural talents and life goals (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Calculate Expression number - Natural talents and life goals. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'calculate',
			'expression'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'fullName' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-gun-milan' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-gun-milan',
		'title' => 'Calculate compatibility score - Gun Milan API (Ashtakoot Matching) (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Calculate compatibility score - Gun Milan API (Ashtakoot Matching). Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'calculate',
			'gun'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'person1' => array(
				'type' => 'string',
				'default' => ''
			),
			'person2' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-houses' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-houses',
		'title' => 'Calculate house cusps - House system calculator with comparison (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Calculate house cusps - House system calculator with comparison. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'calculate',
			'houses'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'houseSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-life-path' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-life-path',
		'title' => 'Calculate Life Path number - Most important numerology calculation (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Calculate Life Path number - Most important numerology calculation. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'calculate',
			'life'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'day' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-maturity' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-maturity',
		'title' => 'Calculate Maturity number - Who you become in later life (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Calculate Maturity number - Who you become in later life. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'calculate',
			'maturity'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lifePath' => array(
				'type' => 'string',
				'default' => ''
			),
			'expression' => array(
				'type' => 'string',
				'default' => ''
			),
			'fullName' => array(
				'type' => 'string',
				'default' => ''
			),
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'day' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-num-compatibility' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-num-compatibility',
		'title' => 'Calculate Compatibility - Relationship dynamics between two people (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Calculate Compatibility - Relationship dynamics between two people. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'calculate',
			'num'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'person1' => array(
				'type' => 'string',
				'default' => ''
			),
			'person2' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-parallels' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-parallels',
		'title' => 'Declination Parallels - Planets at same or opposite declination (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Declination Parallels - Planets at same or opposite declination. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'calculate',
			'parallels'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'orb' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-personal-day' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-personal-day',
		'title' => 'Calculate Personal Day - Daily personalized numerology forecast (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Calculate Personal Day - Daily personalized numerology forecast. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'calculate',
			'personal'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'day' => array(
				'type' => 'string',
				'default' => ''
			),
			'targetDate' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-personal-month' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-personal-month',
		'title' => 'Calculate Personal Month - Monthly numerology forecast (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Calculate Personal Month - Monthly numerology forecast. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'calculate',
			'personal'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'day' => array(
				'type' => 'string',
				'default' => ''
			),
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'targetMonth' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-personal-year' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-personal-year',
		'title' => 'Calculate Personal Year - Annual cycle and forecast for current year (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Calculate Personal Year - Annual cycle and forecast for current year. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'calculate',
			'personal'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'day' => array(
				'type' => 'string',
				'default' => ''
			),
			'year' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-personality' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-personality',
		'title' => 'Calculate Personality number - How others perceive you (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Calculate Personality number - How others perceive you. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'calculate',
			'personality'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'fullName' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-shadbala' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-shadbala',
		'title' => 'Get Shadbala (six-fold planetary strength) analysis - Shadbala Calculator API (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get Shadbala (six-fold planetary strength) analysis - Shadbala Calculator API. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'calculate',
			'shadbala'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-soul-urge' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-soul-urge',
		'title' => 'Calculate Soul Urge number - Inner motivations and desires (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Calculate Soul Urge number - Inner motivations and desires. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'calculate',
			'soul'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'fullName' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-synastry' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-synastry',
		'title' => 'Calculate synastry - Relationship compatibility analysis API (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Calculate synastry - Relationship compatibility analysis API. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'calculate',
			'synastry'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'person1' => array(
				'type' => 'string',
				'default' => ''
			),
			'person2' => array(
				'type' => 'string',
				'default' => ''
			),
			'houseSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-transit' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-transit',
		'title' => 'Transit Analysis - Compare current planets to natal chart (Gochar) (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Transit Analysis - Compare current planets to natal chart (Gochar). Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'calculate',
			'transit'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'birthDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'birthTime' => array(
				'type' => 'string',
				'default' => ''
			),
			'transitDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'transitTime' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'coordinateSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-transit-aspects' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-transit-aspects',
		'title' => 'Transit Aspects - Detailed transit-to-natal aspect analysis with interpretations (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Transit Aspects - Detailed transit-to-natal aspect analysis with interpretations. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'calculate',
			'transit'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'natalChart' => array(
				'type' => 'string',
				'default' => ''
			),
			'transitDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'transitTime' => array(
				'type' => 'string',
				'default' => ''
			),
			'planets' => array(
				'type' => 'string',
				'default' => ''
			),
			'aspectTypes' => array(
				'type' => 'string',
				'default' => ''
			),
			'minStrength' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'calculate-transits' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/calculate-transits',
		'title' => 'Calculate planetary transits - Current transits with natal chart comparison (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Calculate planetary transits - Current transits with natal chart comparison. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'calculate',
			'transits'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'natalChart' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'cast-career-spread' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/cast-career-spread',
		'title' => 'Career Spread (7 cards) (Tarot)',
		'category' => 'roxyapi',
		'icon' => 'heart',
		'description' => 'Career Spread (7 cards). Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'tarot',
			'roxyapi',
			'cast',
			'career'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'question' => array(
				'type' => 'string',
				'default' => ''
			),
			'seed' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'cast-celtic-cross' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/cast-celtic-cross',
		'title' => 'Celtic Cross Spread (10 cards) (Tarot)',
		'category' => 'roxyapi',
		'icon' => 'heart',
		'description' => 'Celtic Cross Spread (10 cards). Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'tarot',
			'roxyapi',
			'cast',
			'celtic'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'question' => array(
				'type' => 'string',
				'default' => ''
			),
			'seed' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'cast-custom-spread' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/cast-custom-spread',
		'title' => 'Custom Spread Builder (Tarot)',
		'category' => 'roxyapi',
		'icon' => 'heart',
		'description' => 'Custom Spread Builder. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'tarot',
			'roxyapi',
			'cast',
			'custom'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'spreadName' => array(
				'type' => 'string',
				'default' => ''
			),
			'positions' => array(
				'type' => 'string',
				'default' => ''
			),
			'question' => array(
				'type' => 'string',
				'default' => ''
			),
			'seed' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'cast-daily-reading' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/cast-daily-reading',
		'title' => 'Cast daily I-Ching reading with changing lines (I-Ching)',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'Cast daily I-Ching reading with changing lines. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'i-ching',
			'roxyapi',
			'cast',
			'daily'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'seed' => array(
				'type' => 'string',
				'default' => ''
			),
			'date' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'cast-love-spread' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/cast-love-spread',
		'title' => 'Love Spread (5 cards) (Tarot)',
		'category' => 'roxyapi',
		'icon' => 'heart',
		'description' => 'Love Spread (5 cards). Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'tarot',
			'roxyapi',
			'cast',
			'love'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'question' => array(
				'type' => 'string',
				'default' => ''
			),
			'seed' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'cast-reading' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/cast-reading',
		'title' => 'Cast an I-Ching reading (I-Ching)',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'Cast an I-Ching reading. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'i-ching',
			'roxyapi',
			'cast',
			'reading'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'seed' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'cast-three-card' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/cast-three-card',
		'title' => 'Three-Card Spread: Past, Present, Future (Tarot)',
		'category' => 'roxyapi',
		'icon' => 'heart',
		'description' => 'Three-Card Spread: Past, Present, Future. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'tarot',
			'roxyapi',
			'cast',
			'three'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'question' => array(
				'type' => 'string',
				'default' => ''
			),
			'seed' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'cast-yes-no' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/cast-yes-no',
		'title' => 'Get yes/no answer to your question (Tarot)',
		'category' => 'roxyapi',
		'icon' => 'heart',
		'description' => 'Get yes/no answer to your question. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'tarot',
			'roxyapi',
			'cast',
			'yes'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'question' => array(
				'type' => 'string',
				'default' => ''
			),
			'seed' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'check-kalsarpa-dosha' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/check-kalsarpa-dosha',
		'title' => 'Check Kalsarpa Dosha - Kalsarpa Yoga Calculator API (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Check Kalsarpa Dosha - Kalsarpa Yoga Calculator API. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'check',
			'kalsarpa'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'check-karmic-debt' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/check-karmic-debt',
		'title' => 'Detect Karmic Debt numbers - Past life challenges (13, 14, 16, 19) (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Detect Karmic Debt numbers - Past life challenges (13, 14, 16, 19). Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'check',
			'karmic'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'day' => array(
				'type' => 'string',
				'default' => ''
			),
			'fullName' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'check-manglik-dosha' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/check-manglik-dosha',
		'title' => 'Check Manglik Dosha - Mangal Dosha Calculator API (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Check Manglik Dosha - Mangal Dosha Calculator API. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'check',
			'manglik'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'check-sadhesati' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/check-sadhesati',
		'title' => 'Check Sadhesati - Sade Sati Calculator API (Saturn Transit) (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Check Sadhesati - Sade Sati Calculator API (Saturn Transit). Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'check',
			'sadhesati'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'draw-cards' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/draw-cards',
		'title' => 'Draw random tarot cards with reproducible results (Tarot)',
		'category' => 'roxyapi',
		'icon' => 'heart',
		'description' => 'Draw random tarot cards with reproducible results. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'tarot',
			'roxyapi',
			'draw',
			'cards'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'count' => array(
				'type' => 'string',
				'default' => ''
			),
			'seed' => array(
				'type' => 'string',
				'default' => ''
			),
			'allowReversals' => array(
				'type' => 'string',
				'default' => ''
			),
			'allowDuplicates' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'generate-birth-chart' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/generate-birth-chart',
		'title' => 'Get birth chart (D1 Rashi chart) - Kundli Calculator API (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get birth chart (D1 Rashi chart) - Kundli Calculator API. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'generate',
			'birth'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'generate-composite-chart' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/generate-composite-chart',
		'title' => 'Composite Chart - Midpoint relationship chart with interpretations (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Composite Chart - Midpoint relationship chart with interpretations. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'generate',
			'composite'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'person1' => array(
				'type' => 'string',
				'default' => ''
			),
			'person2' => array(
				'type' => 'string',
				'default' => ''
			),
			'houseSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'generate-divisional-chart' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/generate-divisional-chart',
		'title' => 'Get divisional chart (Varga) - D2 to D60 Calculator (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get divisional chart (Varga) - D2 to D60 Calculator. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'generate',
			'divisional'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'division' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'generate-kp-chart' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/generate-kp-chart',
		'title' => 'Generate complete KP birth chart (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Generate complete KP birth chart. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'generate',
			'kp'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'ayanamsa' => array(
				'type' => 'string',
				'default' => ''
			),
			'ayanamsaValue' => array(
				'type' => 'string',
				'default' => ''
			),
			'nodeType' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'generate-lunar-return' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/generate-lunar-return',
		'title' => 'Lunar Return Chart - Monthly emotional forecast with Moon cycle chart (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Lunar Return Chart - Monthly emotional forecast with Moon cycle chart. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'generate',
			'lunar'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'birthDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'birthTime' => array(
				'type' => 'string',
				'default' => ''
			),
			'returnDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'houseSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'generate-natal-chart' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/generate-natal-chart',
		'title' => 'Generate natal chart - Birth chart calculator API with houses and aspects (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Generate natal chart - Birth chart calculator API with houses and aspects. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'generate',
			'natal'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'houseSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'generate-navamsa' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/generate-navamsa',
		'title' => 'Get Navamsa chart (D9) - Marriage Compatibility Calculator (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get Navamsa chart (D9) - Marriage Compatibility Calculator. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'generate',
			'navamsa'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'generate-numerology-chart' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/generate-numerology-chart',
		'title' => 'Generate Complete Numerology Chart - Full profile analysis (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Generate Complete Numerology Chart - Full profile analysis. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'generate'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'fullName' => array(
				'type' => 'string',
				'default' => ''
			),
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'day' => array(
				'type' => 'string',
				'default' => ''
			),
			'currentYear' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'generate-planetary-return' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/generate-planetary-return',
		'title' => 'Planetary Return Chart - Saturn return, Jupiter return, and inner planet cycles (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Planetary Return Chart - Saturn return, Jupiter return, and inner planet cycles. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'generate',
			'planetary'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'birthDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'birthTime' => array(
				'type' => 'string',
				'default' => ''
			),
			'planet' => array(
				'type' => 'string',
				'default' => ''
			),
			'approximateDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'houseSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'generate-solar-return' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/generate-solar-return',
		'title' => 'Solar Return Chart - Annual birthday forecast with relocated chart (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Solar Return Chart - Annual birthday forecast with relocated chart. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'generate',
			'solar'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'birthDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'birthTime' => array(
				'type' => 'string',
				'default' => ''
			),
			'returnYear' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'houseSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-angel-number' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-angel-number',
		'title' => 'Get Angel Number Meaning (Angel Numbers)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Get Angel Number Meaning. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'angel numbers',
			'roxyapi',
			'get',
			'angel'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'number' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-basic-panchang' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-basic-panchang',
		'title' => 'Get basic Panchang - Tithi Nakshatra Yoga Karana Calculator (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get basic Panchang - Tithi Nakshatra Yoga Karana Calculator. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'basic'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-birthstones' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-birthstones',
		'title' => 'Birthstone Crystals by Month (Crystals and Healing Stones)',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'Birthstone Crystals by Month. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'crystals and healing stones',
			'roxyapi',
			'get',
			'birthstones'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-card' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-card',
		'title' => 'Get detailed tarot card information (Tarot)',
		'category' => 'roxyapi',
		'icon' => 'heart',
		'description' => 'Get detailed tarot card information. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'tarot',
			'roxyapi',
			'get',
			'card'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'id' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-choghadiya' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-choghadiya',
		'title' => 'Get Choghadiya - 8 Muhurta divisions of day and night (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get Choghadiya - 8 Muhurta divisions of day and night. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'choghadiya'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-cities-by-country' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-cities-by-country',
		'title' => 'Get cities in a country - Geocoding directory sorted by population (Location and Timezone)',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'Get cities in a country - Geocoding directory sorted by population. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'location and timezone',
			'roxyapi',
			'get',
			'cities'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'iso2' => array(
				'type' => 'string',
				'default' => ''
			),
			'limit' => array(
				'type' => 'string',
				'default' => ''
			),
			'offset' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-critical-days' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-critical-days',
		'title' => 'Find critical days - Zero crossing detection for any date range (Biorhythm)',
		'category' => 'roxyapi',
		'icon' => 'tide',
		'description' => 'Find critical days - Zero crossing detection for any date range. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'biorhythm',
			'roxyapi',
			'get',
			'critical'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'birthDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'startDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'endDate' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-crystal' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-crystal',
		'title' => 'Get Crystal Healing Properties (Crystals and Healing Stones)',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'Get Crystal Healing Properties. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'crystals and healing stones',
			'roxyapi',
			'get',
			'crystal'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'id' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-crystal-pairings' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-crystal-pairings',
		'title' => 'Crystal Pairings (Crystals and Healing Stones)',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'Crystal Pairings. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'crystals and healing stones',
			'roxyapi',
			'get',
			'crystal'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'id' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-crystals-by-chakra' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-crystals-by-chakra',
		'title' => 'Crystals by Chakra (Crystals and Healing Stones)',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'Crystals by Chakra. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'crystals and healing stones',
			'roxyapi',
			'get',
			'crystals'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'chakra' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'limit' => array(
				'type' => 'string',
				'default' => ''
			),
			'offset' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-crystals-by-element' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-crystals-by-element',
		'title' => 'Crystals by Element (Crystals and Healing Stones)',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'Crystals by Element. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'crystals and healing stones',
			'roxyapi',
			'get',
			'crystals'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'element' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'limit' => array(
				'type' => 'string',
				'default' => ''
			),
			'offset' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-crystals-by-zodiac' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-crystals-by-zodiac',
		'title' => 'Crystals by Zodiac Sign (Crystals and Healing Stones)',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'Crystals by Zodiac Sign. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'crystals and healing stones',
			'roxyapi',
			'get',
			'crystals'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'sign' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'limit' => array(
				'type' => 'string',
				'default' => ''
			),
			'offset' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-current-dasha' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-current-dasha',
		'title' => 'Get current Mahadasha, Antardasha, Pratyantardasha - Dasha Calculator API (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get current Mahadasha, Antardasha, Pratyantardasha - Dasha Calculator API. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'current'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-current-moon-phase' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-current-moon-phase',
		'title' => 'Get current moon phase - Lunar phase calculator with zodiac sign (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Get current moon phase - Lunar phase calculator with zodiac sign. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'get',
			'current'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-daily-angel-number' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-daily-angel-number',
		'title' => 'Daily Angel Number (Angel Numbers)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Daily Angel Number. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'angel numbers',
			'roxyapi',
			'get',
			'daily'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'seed' => array(
				'type' => 'string',
				'default' => ''
			),
			'date' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-daily-biorhythm' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-daily-biorhythm',
		'title' => 'Get daily biorhythm - Seeded reading for daily check-in features (Biorhythm)',
		'category' => 'roxyapi',
		'icon' => 'tide',
		'description' => 'Get daily biorhythm - Seeded reading for daily check-in features. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'biorhythm',
			'roxyapi',
			'get',
			'daily'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'seed' => array(
				'type' => 'string',
				'default' => ''
			),
			'date' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-daily-card' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-daily-card',
		'title' => 'Get daily tarot card reading (Tarot)',
		'category' => 'roxyapi',
		'icon' => 'heart',
		'description' => 'Get daily tarot card reading. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'tarot',
			'roxyapi',
			'get',
			'daily'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'seed' => array(
				'type' => 'string',
				'default' => ''
			),
			'date' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-daily-crystal' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-daily-crystal',
		'title' => 'Daily Crystal (Crystals and Healing Stones)',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'Daily Crystal. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'crystals and healing stones',
			'roxyapi',
			'get',
			'daily'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'seed' => array(
				'type' => 'string',
				'default' => ''
			),
			'date' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-daily-dream-symbol' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-daily-dream-symbol',
		'title' => 'Get daily dream symbol (Dreams)',
		'category' => 'roxyapi',
		'icon' => 'book',
		'description' => 'Get daily dream symbol. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'dreams',
			'roxyapi',
			'get',
			'daily'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'seed' => array(
				'type' => 'string',
				'default' => ''
			),
			'date' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-daily-hexagram' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-daily-hexagram',
		'title' => 'Get daily I-Ching hexagram (I-Ching)',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'Get daily I-Ching hexagram. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'i-ching',
			'roxyapi',
			'get',
			'daily'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'seed' => array(
				'type' => 'string',
				'default' => ''
			),
			'date' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-daily-number' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-daily-number',
		'title' => 'Get daily numerology number - Number of the Day with interpretation (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Get daily numerology number - Number of the Day with interpretation. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'get',
			'daily'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'seed' => array(
				'type' => 'string',
				'default' => ''
			),
			'date' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-detailed-panchang' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-detailed-panchang',
		'title' => 'Get detailed Panchang with Rahu Kaal, Yamaganda, Gulika (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get detailed Panchang with Rahu Kaal, Yamaganda, Gulika. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'detailed'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-dream-symbol' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-dream-symbol',
		'title' => 'Get dream symbol details (Dreams)',
		'category' => 'roxyapi',
		'icon' => 'book',
		'description' => 'Get dream symbol details. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'dreams',
			'roxyapi',
			'get',
			'dream'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'id' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-ecliptic-crossings' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-ecliptic-crossings',
		'title' => 'Ecliptic Crossings - When planets cross the ecliptic plane (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Ecliptic Crossings - When planets cross the ecliptic plane. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'ecliptic'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'coordinateSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-forecast' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-forecast',
		'title' => 'Get biorhythm forecast - Multi-day cycle predictions with best and worst days (Biorhythm)',
		'category' => 'roxyapi',
		'icon' => 'tide',
		'description' => 'Get biorhythm forecast - Multi-day cycle predictions with best and worst days. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'biorhythm',
			'roxyapi',
			'get',
			'forecast'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'birthDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'startDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'endDate' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-hexagram' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-hexagram',
		'title' => 'Get hexagram by number (I-Ching)',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'Get hexagram by number. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'i-ching',
			'roxyapi',
			'get',
			'hexagram'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'number' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-hora' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-hora',
		'title' => 'Get Hora - 24 Planetary Hours (12 day + 12 night) (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get Hora - 24 Planetary Hours (12 day + 12 night). Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'hora'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-kp-ayanamsa' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-kp-ayanamsa',
		'title' => 'Get KP-Newcomb ayanamsa - Dynamic daily calculation (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get KP-Newcomb ayanamsa - Dynamic daily calculation. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'kp'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-kp-cusps' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-kp-cusps',
		'title' => 'Get KP Placidus house cusps with sub-lords (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get KP Placidus house cusps with sub-lords. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'kp'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'ayanamsa' => array(
				'type' => 'string',
				'default' => ''
			),
			'ayanamsaValue' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-kp-planets' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-kp-planets',
		'title' => 'Get KP planetary positions with sub-lords (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get KP planetary positions with sub-lords. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'kp'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'ayanamsa' => array(
				'type' => 'string',
				'default' => ''
			),
			'ayanamsaValue' => array(
				'type' => 'string',
				'default' => ''
			),
			'nodeType' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-kp-planets-interval' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-kp-planets-interval',
		'title' => 'Get KP planets at time intervals (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get KP planets at time intervals. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'kp'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'startDatetime' => array(
				'type' => 'string',
				'default' => ''
			),
			'endDatetime' => array(
				'type' => 'string',
				'default' => ''
			),
			'intervalMinutes' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'ayanamsa' => array(
				'type' => 'string',
				'default' => ''
			),
			'nodeType' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-kp-rasi-changes' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-kp-rasi-changes',
		'title' => 'Find KP rasi ingress times (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Find KP rasi ingress times. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'kp'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'planet' => array(
				'type' => 'string',
				'default' => ''
			),
			'startDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'endDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'ayanamsa' => array(
				'type' => 'string',
				'default' => ''
			),
			'nodeType' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-kp-ruling-interval' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-kp-ruling-interval',
		'title' => 'Get KP ruling planets with significators at intervals (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get KP ruling planets with significators at intervals. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'kp'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'startDatetime' => array(
				'type' => 'string',
				'default' => ''
			),
			'endDatetime' => array(
				'type' => 'string',
				'default' => ''
			),
			'intervalMinutes' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'ayanamsa' => array(
				'type' => 'string',
				'default' => ''
			),
			'nodeType' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-kp-ruling-planets' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-kp-ruling-planets',
		'title' => 'Get KP ruling planets with optional significators (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get KP ruling planets with optional significators. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'kp'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'datetime' => array(
				'type' => 'string',
				'default' => ''
			),
			'birthDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'birthTime' => array(
				'type' => 'string',
				'default' => ''
			),
			'nodeType' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-kp-sublord-changes' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-kp-sublord-changes',
		'title' => 'Find KP sublord changes (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Find KP sublord changes. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'kp'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'planet' => array(
				'type' => 'string',
				'default' => ''
			),
			'startDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'endDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'ayanamsa' => array(
				'type' => 'string',
				'default' => ''
			),
			'nodeType' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-lunar-aspects' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-lunar-aspects',
		'title' => 'Monthly Lunar Aspects - Moon aspect events with all planets for a month (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Monthly Lunar Aspects - Moon aspect events with all planets for a month. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'lunar'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'coordinateSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-major-dashas' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-major-dashas',
		'title' => 'Get all 9 Mahadasha periods (120-year cycle) (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get all 9 Mahadasha periods (120-year cycle). Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'major'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-monthly-aspects' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-monthly-aspects',
		'title' => 'Monthly Planetary Aspects - Major and minor aspect events for a month (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Monthly Planetary Aspects - Major and minor aspect events for a month. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'monthly'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'coordinateSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-monthly-ephemeris' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-monthly-ephemeris',
		'title' => 'Monthly Ephemeris - Daily sidereal planetary positions for a month (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Monthly Ephemeris - Daily sidereal planetary positions for a month. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'monthly'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'coordinateSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-monthly-horoscope' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-monthly-horoscope',
		'title' => 'Monthly horoscope by zodiac sign - 30-day transit forecast with key dates (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Monthly horoscope by zodiac sign - 30-day transit forecast with key dates. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'get',
			'monthly'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'sign' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-monthly-parallels' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-monthly-parallels',
		'title' => 'Monthly Declination Parallels - Parallel and contraparallel events for a month (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Monthly Declination Parallels - Parallel and contraparallel events for a month. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'monthly'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-monthly-transits' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-monthly-transits',
		'title' => 'Monthly Transit - Planetary sign changes for an entire month (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Monthly Transit - Planetary sign changes for an entire month. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'monthly'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			),
			'coordinateSystem' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-moon-calendar' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-moon-calendar',
		'title' => 'Get lunar calendar - Moon phases for entire month (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Get lunar calendar - Moon phases for entire month. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'get',
			'moon'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'year' => array(
				'type' => 'string',
				'default' => ''
			),
			'month' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-nakshatra' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-nakshatra',
		'title' => 'Get Nakshatra by ID - Lunar Mansion Detail (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get Nakshatra by ID - Lunar Mansion Detail. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'nakshatra'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'id' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-number-meaning' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-number-meaning',
		'title' => 'Get Number Meaning - Interpretation for any number 1-9, 11, 22, 33 (Numerology)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'Get Number Meaning - Interpretation for any number 1-9, 11, 22, 33. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'numerology',
			'roxyapi',
			'get',
			'number'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'number' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-phases' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-phases',
		'title' => 'Get phase info - Lightweight cycle status for dashboards and widgets (Biorhythm)',
		'category' => 'roxyapi',
		'icon' => 'tide',
		'description' => 'Get phase info - Lightweight cycle status for dashboards and widgets. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'biorhythm',
			'roxyapi',
			'get',
			'phases'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'birthDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'targetDate' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-planet-meaning' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-planet-meaning',
		'title' => 'Get planet meaning details - Complete astrology planet interpretation (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Get planet meaning details - Complete astrology planet interpretation. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'get',
			'planet'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'id' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-planet-positions' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-planet-positions',
		'title' => 'Get planetary positions - Graha Positions API (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get planetary positions - Graha Positions API. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'planet'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-planetary-positions' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-planetary-positions',
		'title' => 'Get planetary positions - Ephemeris calculator for all planets (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Get planetary positions - Ephemeris calculator for all planets. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'get',
			'planetary'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-random-crystal' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-random-crystal',
		'title' => 'Random Crystal (Crystals and Healing Stones)',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'Random Crystal. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'crystals and healing stones',
			'roxyapi',
			'get',
			'random'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-random-hexagram' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-random-hexagram',
		'title' => 'Get a random hexagram (I-Ching)',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'Get a random hexagram. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'i-ching',
			'roxyapi',
			'get',
			'random'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-random-symbols' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-random-symbols',
		'title' => 'Get random dream symbols (Dreams)',
		'category' => 'roxyapi',
		'icon' => 'book',
		'description' => 'Get random dream symbols. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'dreams',
			'roxyapi',
			'get',
			'random'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'count' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-rashi' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-rashi',
		'title' => 'Get Rashi by ID - Vedic Zodiac Sign Detail (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get Rashi by ID - Vedic Zodiac Sign Detail. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'rashi'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'id' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-reading' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-reading',
		'title' => 'Get biorhythm reading - Complete cycle analysis for any date (Biorhythm)',
		'category' => 'roxyapi',
		'icon' => 'tide',
		'description' => 'Get biorhythm reading - Complete cycle analysis for any date. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'biorhythm',
			'roxyapi',
			'get',
			'reading'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'birthDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'targetDate' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-sub-dashas' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-sub-dashas',
		'title' => 'Get all Antardashas (sub-periods) for a specific Mahadasha (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get all Antardashas (sub-periods) for a specific Mahadasha. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'sub'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-symbol-letter-counts' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-symbol-letter-counts',
		'title' => 'Get symbol counts by letter (Dreams)',
		'category' => 'roxyapi',
		'icon' => 'book',
		'description' => 'Get symbol counts by letter. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'dreams',
			'roxyapi',
			'get',
			'symbol'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			
		),
		'render' => 'file:./render.php'
	),
	'get-trigram' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-trigram',
		'title' => 'Get trigram by number or name (I-Ching)',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'Get trigram by number or name. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'i-ching',
			'roxyapi',
			'get',
			'trigram'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'id' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-upagraha-positions' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-upagraha-positions',
		'title' => 'Get upagraha (sub-planet) positions - Upagraha Calculator API (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get upagraha (sub-planet) positions - Upagraha Calculator API. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'upagraha'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'date' => array(
				'type' => 'string',
				'default' => ''
			),
			'time' => array(
				'type' => 'string',
				'default' => ''
			),
			'latitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'longitude' => array(
				'type' => 'string',
				'default' => ''
			),
			'timezone' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-upcoming-moon-phases' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-upcoming-moon-phases',
		'title' => 'Get upcoming moon phases - Next new moon, full moon, quarters (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Get upcoming moon phases - Next new moon, full moon, quarters. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'get',
			'upcoming'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'startDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'count' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-usage-stats' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-usage-stats',
		'title' => 'Get API usage statistics (Usage)',
		'category' => 'roxyapi',
		'icon' => 'star-half',
		'description' => 'Get API usage statistics. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'usage',
			'roxyapi',
			'get'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			
		),
		'render' => 'file:./render.php'
	),
	'get-weekly-horoscope' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-weekly-horoscope',
		'title' => 'Weekly horoscope by zodiac sign - 7-day transit forecast (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Weekly horoscope by zodiac sign - 7-day transit forecast. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'get',
			'weekly'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'sign' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-yoga' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-yoga',
		'title' => 'Get yoga details by ID - Detailed Yoga Information API (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'Get yoga details by ID - Detailed Yoga Information API. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'get',
			'yoga'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'id' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'get-zodiac-sign' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/get-zodiac-sign',
		'title' => 'Get zodiac sign details - Complete astrology sign profile with personality traits (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Get zodiac sign details - Complete astrology sign profile with personality traits. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'get',
			'zodiac'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'id' => array(
				'type' => 'string',
				'default' => ''
			),
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'list-angel-numbers' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-angel-numbers',
		'title' => 'List All Angel Numbers (Angel Numbers)',
		'category' => 'roxyapi',
		'icon' => 'chart-line',
		'description' => 'List All Angel Numbers. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'angel numbers',
			'roxyapi',
			'list',
			'angel'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'limit' => array(
				'type' => 'string',
				'default' => ''
			),
			'offset' => array(
				'type' => 'string',
				'default' => ''
			),
			'type' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'list-cards' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-cards',
		'title' => 'List all 78 tarot cards (Tarot)',
		'category' => 'roxyapi',
		'icon' => 'heart',
		'description' => 'List all 78 tarot cards. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'tarot',
			'roxyapi',
			'list',
			'cards'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'limit' => array(
				'type' => 'string',
				'default' => ''
			),
			'offset' => array(
				'type' => 'string',
				'default' => ''
			),
			'arcana' => array(
				'type' => 'string',
				'default' => ''
			),
			'suit' => array(
				'type' => 'string',
				'default' => ''
			),
			'number' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'list-countries' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-countries',
		'title' => 'List all 227 countries - ISO codes and city coverage (Location and Timezone)',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'List all 227 countries - ISO codes and city coverage. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'location and timezone',
			'roxyapi',
			'list',
			'countries'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'limit' => array(
				'type' => 'string',
				'default' => ''
			),
			'offset' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'list-crystal-colors' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-crystal-colors',
		'title' => 'List Crystal Colors (Crystals and Healing Stones)',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'List Crystal Colors. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'crystals and healing stones',
			'roxyapi',
			'list',
			'crystal'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			
		),
		'render' => 'file:./render.php'
	),
	'list-crystal-planets' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-crystal-planets',
		'title' => 'List Crystal Planets (Crystals and Healing Stones)',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'List Crystal Planets. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'crystals and healing stones',
			'roxyapi',
			'list',
			'crystal'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			
		),
		'render' => 'file:./render.php'
	),
	'list-crystals' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-crystals',
		'title' => 'List All Crystals (Crystals and Healing Stones)',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'List All Crystals. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'crystals and healing stones',
			'roxyapi',
			'list',
			'crystals'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'chakra' => array(
				'type' => 'string',
				'default' => ''
			),
			'zodiac' => array(
				'type' => 'string',
				'default' => ''
			),
			'element' => array(
				'type' => 'string',
				'default' => ''
			),
			'color' => array(
				'type' => 'string',
				'default' => ''
			),
			'planet' => array(
				'type' => 'string',
				'default' => ''
			),
			'limit' => array(
				'type' => 'string',
				'default' => ''
			),
			'offset' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'list-hexagrams' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-hexagrams',
		'title' => 'List all 64 hexagrams (I-Ching)',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'List all 64 hexagrams. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'i-ching',
			'roxyapi',
			'list',
			'hexagrams'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'limit' => array(
				'type' => 'string',
				'default' => ''
			),
			'offset' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'list-nakshatras' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-nakshatras',
		'title' => 'List all 27 Nakshatras - Lunar Mansions Reference (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'List all 27 Nakshatras - Lunar Mansions Reference. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'list',
			'nakshatras'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'list-planet-meanings' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-planet-meanings',
		'title' => 'Get all planet meanings - Complete astrology planet interpretations list (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Get all planet meanings - Complete astrology planet interpretations list. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'list',
			'planet'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'list-rashis' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-rashis',
		'title' => 'List all 12 Rashis - Vedic Zodiac Signs Reference (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'List all 12 Rashis - Vedic Zodiac Signs Reference. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'list',
			'rashis'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'list-trigrams' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-trigrams',
		'title' => 'List all 8 trigrams (I-Ching)',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'List all 8 trigrams. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'i-ching',
			'roxyapi',
			'list',
			'trigrams'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'list-yogas' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-yogas',
		'title' => 'List all planetary yogas - 300+ Vedic Yoga Combinations (Vedic Astrology)',
		'category' => 'roxyapi',
		'icon' => 'database',
		'description' => 'List all planetary yogas - 300+ Vedic Yoga Combinations. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'vedic astrology',
			'roxyapi',
			'list',
			'yogas'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'list-zodiac-signs' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/list-zodiac-signs',
		'title' => 'Get all zodiac signs - Complete zodiac signs list with dates and elements (Western Astrology)',
		'category' => 'roxyapi',
		'icon' => 'admin-customizer',
		'description' => 'Get all zodiac signs - Complete zodiac signs list with dates and elements. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'western astrology',
			'roxyapi',
			'list',
			'zodiac'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'lookup-hexagram' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/lookup-hexagram',
		'title' => 'Lookup hexagram by line pattern (I-Ching)',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'Lookup hexagram by line pattern. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'i-ching',
			'roxyapi',
			'lookup',
			'hexagram'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'lines' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'search-cities' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/search-cities',
		'title' => 'Search cities worldwide - Geocoding autocomplete with coordinates and timezone (Location and Timezone)',
		'category' => 'roxyapi',
		'icon' => 'superhero',
		'description' => 'Search cities worldwide - Geocoding autocomplete with coordinates and timezone. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'location and timezone',
			'roxyapi',
			'search',
			'cities'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'q' => array(
				'type' => 'string',
				'default' => ''
			),
			'limit' => array(
				'type' => 'string',
				'default' => ''
			),
			'offset' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'search-crystals' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/search-crystals',
		'title' => 'Search Crystals (Crystals and Healing Stones)',
		'category' => 'roxyapi',
		'icon' => 'star-filled',
		'description' => 'Search Crystals. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'crystals and healing stones',
			'roxyapi',
			'search',
			'crystals'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'lang' => array(
				'type' => 'string',
				'default' => ''
			),
			'q' => array(
				'type' => 'string',
				'default' => ''
			),
			'limit' => array(
				'type' => 'string',
				'default' => ''
			),
			'offset' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	),
	'search-dream-symbols' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'roxyapi/search-dream-symbols',
		'title' => 'List and search dream symbols (Dreams)',
		'category' => 'roxyapi',
		'icon' => 'book',
		'description' => 'List and search dream symbols. Auto-generated from the RoxyAPI OpenAPI spec.',
		'keywords' => array(
			'dreams',
			'roxyapi',
			'search',
			'dream'
		),
		'version' => '1.0.0',
		'textdomain' => 'roxyapi',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			)
		),
		'attributes' => array(
			'q' => array(
				'type' => 'string',
				'default' => ''
			),
			'letter' => array(
				'type' => 'string',
				'default' => ''
			),
			'limit' => array(
				'type' => 'string',
				'default' => ''
			),
			'offset' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'render' => 'file:./render.php'
	)
);
