/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Panel, Title, Save, InputGroup, getCommonValue } from './shared';

function BackgroundColorPanel( { selectedElements, onSetProperties } ) {
	const backgroundColor = getCommonValue( selectedElements, 'backgroundColor' );
	const [ state, setState ] = useState( { backgroundColor } );
	useEffect( () => {
		setState( { backgroundColor } );
	}, [ backgroundColor ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};
	return (
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ 'Background color' }
			</Title>
			<InputGroup
				label="Background color"
				value={ state.backgroundColor }
				isMultiple={ backgroundColor === '' }
				onChange={ ( value ) => setState( { ...state, backgroundColor: value } ) }
			/>
			<Save>
				{ 'Save' }
			</Save>
		</Panel>
	);
}

BackgroundColorPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default BackgroundColorPanel;
