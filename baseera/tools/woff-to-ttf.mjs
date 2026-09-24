/**
 * يحوّل WOFF (الإصدار الأول) إلى TTF.
 *
 * resvg لا يقرأ إلا TTF/OTF، و@fontsource يشحن WOFF/WOFF2 فقط. وWOFF1 بسيط:
 * ترويسة، ودليل جداول، وكل جدول مضغوط بـ zlib أو مخزَّن كما هو. فكّه لا
 * يحتاج مكتبة: نعيد بناء ترويسة sfnt ونفكّ كل جدول في مكانه.
 */
import { inflateSync } from 'node:zlib';

export function woffToTtf( woff ) {
	const view = new DataView( woff.buffer, woff.byteOffset, woff.byteLength );

	if ( view.getUint32( 0 ) !== 0x774f4646 ) {
		throw new Error( 'ليس ملفّ WOFF' );
	}

	const flavor = view.getUint32( 4 );
	const numTables = view.getUint16( 12 );
	const tables = [];

	for ( let i = 0; i < numTables; i++ ) {
		const o = 44 + i * 20;
		tables.push( {
			tag: view.getUint32( o ),
			offset: view.getUint32( o + 4 ),
			compLength: view.getUint32( o + 8 ),
			origLength: view.getUint32( o + 12 ),
			checksum: view.getUint32( o + 16 ),
		} );
	}

	const data = tables.map( ( t ) => {
		const slice = woff.subarray( t.offset, t.offset + t.compLength );

		return t.compLength < t.origLength ? inflateSync( slice ) : Buffer.from( slice );
	} );

	let entrySelector = 0;

	while ( 2 ** ( entrySelector + 1 ) <= numTables ) {
		entrySelector++;
	}

	const searchRange = 2 ** entrySelector * 16;
	const headerSize = 12 + numTables * 16;
	let size = headerSize;

	for ( const d of data ) {
		size += ( d.length + 3 ) & ~3;
	}

	const out = Buffer.alloc( size );
	out.writeUInt32BE( flavor, 0 );
	out.writeUInt16BE( numTables, 4 );
	out.writeUInt16BE( searchRange, 6 );
	out.writeUInt16BE( entrySelector, 8 );
	out.writeUInt16BE( numTables * 16 - searchRange, 10 );

	let cursor = headerSize;

	tables.forEach( ( t, i ) => {
		const o = 12 + i * 16;
		out.writeUInt32BE( t.tag, o );
		out.writeUInt32BE( t.checksum, o + 4 );
		out.writeUInt32BE( cursor, o + 8 );
		out.writeUInt32BE( t.origLength, o + 12 );
		data[ i ].copy( out, cursor );
		cursor += ( data[ i ].length + 3 ) & ~3;
	} );

	return out;
}
