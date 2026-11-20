/**
 * The join between the pinned @roxyapi/ui catalogue and bin/component-map.json,
 * kept apart from the network so the guard and its test read one rule.
 *
 * The catalogue can bind several components to ONE operation (the kundli and
 * its planets table both draw a birth chart response), so a binding is the
 * pair (operationId, tag), never the operationId alone: keyed by operation, a
 * second component overwrote the first and its missing row passed in silence.
 */

/**
 * Every component the catalogue binds to each operation, in catalogue order.
 *
 * @param {Array<Object>} components Catalogue `components`.
 * @return {Map<string, Array<{tag: string, attrs: Object}>>} operationId to its bindings.
 */
export function catalogBindings( components ) {
	const bindings = new Map();
	for ( const component of components ) {
		for ( const endpoint of component.endpoints || [] ) {
			if ( ! endpoint?.operationId ) {
				continue;
			}
			if ( ! bindings.has( endpoint.operationId ) ) {
				bindings.set( endpoint.operationId, [] );
			}
			bindings.get( endpoint.operationId ).push( {
				tag: component.tag,
				attrs: endpoint.attrs || {},
			} );
		}
	}
	return bindings;
}

/**
 * Where the map falls behind the catalogue.
 *
 * A binding is covered by a row, or by a row whose component draws it inside
 * itself (`nests`), which is why it must not stack: stacking it would show the
 * same table twice. Rows the catalogue binds keep catalogue order, so the
 * chart leads and what it pairs with follows.
 *
 * @param {Map<string, Array<{tag: string, attrs: Object}>>} bindings   From catalogBindings().
 * @param {Object<string, Array<Object>>}                    operations component-map.json `operations`.
 * @param {Set<string>}                                      skip       Operations owed no row (reports).
 * @return {{unbound: string[], nestFaults: string[], misordered: string[], attrDrift: string[]}} One line per fault.
 */
export function bindingGaps( bindings, operations, skip = new Set() ) {
	const unbound = [];
	const nestFaults = [];
	const misordered = [];
	const attrDrift = [];
	for ( const [ op, want ] of bindings ) {
		if ( skip.has( op ) ) {
			continue;
		}
		const rows = operations[ op ] || [];
		const nested = new Set( rows.flatMap( ( row ) => row.nests || [] ) );
		for ( const { tag, attrs } of want ) {
			const row = rows.find( ( r ) => r.component === tag );
			if ( ! row ) {
				if ( ! nested.has( tag ) ) {
					unbound.push( `${ op } -> ${ tag }` );
				}
				continue;
			}
			for ( const [ key, value ] of Object.entries( attrs ) ) {
				if ( ( row.attrs || {} )[ key ] !== value ) {
					attrDrift.push(
						`${ op }: ${ tag } needs ${ key }="${ value }"`
					);
				}
			}
		}
		const wanted = want.map( ( binding ) => binding.tag );
		for ( const tag of nested ) {
			if ( ! wanted.includes( tag ) ) {
				nestFaults.push( `${ op }: ${ tag } is no longer bound here` );
			} else if ( rows.some( ( row ) => row.component === tag ) ) {
				nestFaults.push(
					`${ op }: ${ tag } is nested and stacked, so it renders twice`
				);
			}
		}
		const order = rows
			.map( ( row ) => row.component )
			.filter( ( tag ) => wanted.includes( tag ) );
		const expected = wanted.filter( ( tag ) => order.includes( tag ) );
		if ( order.join() !== expected.join() ) {
			misordered.push( `${ op }: ${ expected.join( ', ' ) }` );
		}
	}
	for ( const [ op, rows ] of Object.entries( operations ) ) {
		if ( ! bindings.has( op ) && rows.some( ( row ) => row.nests ) ) {
			nestFaults.push( `${ op }: no longer bound to any component` );
		}
	}
	return {
		unbound: unbound.sort(),
		nestFaults: nestFaults.sort(),
		misordered: misordered.sort(),
		attrDrift: attrDrift.sort(),
	};
}
