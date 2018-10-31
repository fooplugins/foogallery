import classnames from 'classnames';

const { __ } = wp.i18n;
const { Component } = wp.element;
const { Placeholder } = wp.components;

export default class FooGalleryPlaceholder extends Component {
	render(){
		const { className, children, ...props } = this.props;
		return (
				<Placeholder className={ classnames("editor-media-placeholder", className) } {...props}>
					{children}
				</Placeholder>
		);
	}
}

FooGalleryPlaceholder.defaultProps = {
	className: ""
};