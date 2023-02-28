import classnames from 'classnames';
import { Component } from '@wordpress/element';
import { Placeholder } from '@wordpress/components';

export default class FooGalleryEditPlaceholder extends Component {
	render(){
		const { className, children, ...props } = this.props;
		return (
				<Placeholder className={ classnames("editor-media-placeholder foogallery__placeholder", className) } {...props}>
					{children}
				</Placeholder>
		);
	}
}