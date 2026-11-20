import assert from 'node:assert/strict';
import { test } from 'node:test';
import { bindingGaps, catalogBindings } from './component-bindings.mjs';

const catalog = [
	{
		tag: 'roxy-chart',
		endpoints: [ { operationId: 'drawChart' } ],
	},
	{
		tag: 'roxy-table',
		endpoints: [
			{ operationId: 'drawChart' },
			{ operationId: 'listRows', attrs: { mode: 'rows' } },
		],
	},
];
const bindings = catalogBindings( catalog );
const clean = { unbound: [], nestFaults: [], misordered: [], attrDrift: [] };

test( 'a second component on one operation is a binding of its own', () => {
	assert.deepEqual(
		bindings.get( 'drawChart' ).map( ( b ) => b.tag ),
		[ 'roxy-chart', 'roxy-table' ]
	);
	const gaps = bindingGaps( bindings, {
		drawChart: [ { component: 'roxy-chart' } ],
		listRows: [ { component: 'roxy-table', attrs: { mode: 'rows' } } ],
	} );
	assert.deepEqual( gaps.unbound, [ 'drawChart -> roxy-table' ] );
} );

test( 'stacked in catalogue order or nested, every binding is covered', () => {
	const listRows = [ { component: 'roxy-table', attrs: { mode: 'rows' } } ];
	assert.deepEqual(
		bindingGaps( bindings, {
			drawChart: [
				{ component: 'roxy-chart' },
				{ component: 'roxy-table' },
			],
			listRows,
		} ),
		clean
	);
	assert.deepEqual(
		bindingGaps( bindings, {
			drawChart: [ { component: 'roxy-chart', nests: [ 'roxy-table' ] } ],
			listRows,
		} ),
		clean
	);
} );

test( 'out of order, doubled, stale and attribute faults are named', () => {
	const gaps = bindingGaps( bindings, {
		drawChart: [
			{ component: 'roxy-table' },
			{ component: 'roxy-chart', nests: [ 'roxy-table', 'roxy-gone' ] },
		],
		listRows: [ { component: 'roxy-table' } ],
		retired: [ { component: 'roxy-chart', nests: [ 'roxy-table' ] } ],
	} );
	assert.deepEqual( gaps.misordered, [
		'drawChart: roxy-chart, roxy-table',
	] );
	assert.deepEqual( gaps.nestFaults, [
		'drawChart: roxy-gone is no longer bound here',
		'drawChart: roxy-table is nested and stacked, so it renders twice',
		'retired: no longer bound to any component',
	] );
	assert.deepEqual( gaps.attrDrift, [
		'listRows: roxy-table needs mode="rows"',
	] );
} );

test( 'an operation owed no row is skipped', () => {
	assert.deepEqual(
		bindingGaps(
			bindings,
			{
				listRows: [
					{ component: 'roxy-table', attrs: { mode: 'rows' } },
				],
			},
			new Set( [ 'drawChart' ] )
		),
		clean
	);
} );
