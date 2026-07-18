import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "lang",
		"control": "select",
		"label": "Lang",
		"required": false,
		"help": "Response language (ISO 639-1)",
		"options": [
			"en",
			"tr",
			"de",
			"es",
			"hi",
			"pt",
			"fr",
			"ru"
		]
	},
	{
		"name": "chakra",
		"control": "select",
		"label": "Chakra",
		"required": false,
		"help": "Filter by chakra association, case-insensitive",
		"options": [
			"Root",
			"Sacral",
			"Solar Plexus",
			"Heart",
			"Throat",
			"Third Eye",
			"Crown"
		]
	},
	{
		"name": "zodiac",
		"control": "select",
		"label": "Zodiac",
		"required": false,
		"help": "Filter by zodiac sign, case-insensitive",
		"options": [
			"aries",
			"taurus",
			"gemini",
			"cancer",
			"leo",
			"virgo",
			"libra",
			"scorpio",
			"sagittarius",
			"capricorn",
			"aquarius",
			"pisces"
		]
	},
	{
		"name": "element",
		"control": "select",
		"label": "Element",
		"required": false,
		"help": "Filter by elemental association, case-insensitive",
		"options": [
			"Earth",
			"Water",
			"Fire",
			"Air",
			"Storm"
		]
	},
	{
		"name": "color",
		"control": "text",
		"label": "Color",
		"required": false,
		"help": "Filter by crystal color (partial match, case-insensitive)"
	},
	{
		"name": "planet",
		"control": "text",
		"label": "Planet",
		"required": false,
		"help": "Filter by planetary association (partial match, case-insensitive)"
	},
	{
		"name": "limit",
		"control": "number",
		"label": "Limit",
		"required": false,
		"help": "Maximum items to return per page"
	},
	{
		"name": "offset",
		"control": "number",
		"label": "Offset",
		"required": false,
		"help": "Number of items to skip for pagination"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
