import assert from 'node:assert/strict';
import { test } from 'node:test';
import { branch, newerBranch } from './wp-version.mjs';

test( 'a release candidate on a newer branch is due', () => {
	assert.equal( newerBranch( '7.1', [ '7.2-RC3', '7.1.5' ] ), '7.2' );
} );

test( 'a stable release on a newer branch is due', () => {
	assert.equal( newerBranch( '7.1', [ '7.2', '7.1.5' ] ), '7.2' );
} );

test( 'the same branch is current, whatever its patch level', () => {
	assert.equal( newerBranch( '7.1', [ '7.1.5', '7.0.9' ] ), null );
} );

test( 'a readme ahead of the stable release is current', () => {
	assert.equal( newerBranch( '7.2', [ '7.1.5' ] ), null );
} );

test( 'branches compare as numbers, across a major', () => {
	assert.equal( newerBranch( '7.9', [ '7.10' ] ), '7.10' );
	assert.equal( newerBranch( '7.9', [ '8.0-RC1', '7.9.2' ] ), '8.0' );
	assert.equal( newerBranch( '8.0', [ '7.9.2' ] ), null );
} );

test( 'a malformed version or an empty answer fails, never passes', () => {
	assert.throws( () => branch( 'trunk' ), /Not a WordPress version/ );
	assert.throws( () => newerBranch( '', [ '7.2' ] ), /Not a WordPress/ );
	assert.throws( () => newerBranch( '7.1', [] ), /offered no version/ );
} );
