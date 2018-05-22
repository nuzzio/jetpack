/* global wp:true */
/* global Object:true */
/* global markdownit:true */
/* global React:true */
/* eslint-disable react/jsx-no-bind */
/* eslint-disable no-console */
/**
 * External dependencies
 */

/**
 * Internal dependencies
 */
const { __ } = wp.i18n;

const {
	registerBlockType,
	BlockControls,
} = wp.blocks;

const {
	withState,
	SandBox,
	CodeEditor
} = wp.components;

const { RawHTML } = wp.element;

const el = wp.element.createElement;

const settings = window.Object.assign(
	{
		codemirror: {
			name: 'GitHub Flavored Markdown',
			mime: 'text/x-gfm',
			mode: 'gfm',
			lint: false,
		}
	},
	window._wpGutenbergCodeEditorSettings
);

const md = markdownit(
	{
		html: true,
		linkify: true,
		typographer: true,
	}
);

registerBlockType( 'jetpack/markdown', {

	title: 'Markdown',

	description: [
		__( 'Write your content in plain-text Markdown syntax.' ),
		(
		<p>
			<a href="https://en.support.wordpress.com/markdown-quick-reference/">
				Support Reference
			</a>
		</p>
		)
	],

	icon: el(
		'svg',
		{
			xmlns: 'http://www.w3.org/2000/svg',
			'class': 'dashicons',
			width: '208',
			height: '128',
			viewBox: '0 0 208 128',
			stroke: 'currentColor'
		},
		el(
			'rect',
			{
				width: '198',
				height: '118',
				x: '5',
				y: '5',
				ry: '10',
				'stroke-width': '10',
				fill: 'none'
			}
		),
		el(
			'path', { d: 'M30 98v-68h20l20 25 20-25h20v68h-20v-39l-20 25-20-25v39zM155 98l-30-33h20v-35h20v35h20z' }
		)
	),

	category: 'formatting',

	attributes: {
		markdown: {
			type: 'string',
			source: 'attribute',
			selector: 'div',
			property: 'data-markdown',
		},
		html: {
			type: 'string',
			source: 'html',
			selector: 'div',
		},
	},

	transforms: {
		from: [
			{
				type: 'raw',
				isMatch: ( node ) => node.nodeName === 'IFRAME',
			},
		],
	},

	supports: {
		customClassName: false,
		html: false,
	},

	edit: withState(
		{
			preview: false,
		} )( ( { attributes, setAttributes, setState, isSelected, toggleSelection, preview }, className ) => (
		<div className={ className }>
			<BlockControls>
				<div className="components-toolbar">
					<button
						className={ `components-tab-button ${ ! preview ? 'is-active' : '' }` }
						onClick={ () => setState( { preview: false } ) }
					>
						<span>Markdown</span>
					</button>
					<button
						className={ `components-tab-button ${ preview ? 'is-active' : '' }` }
						onClick={ () => {
							setState( { preview: true } );
						} }
					>
						<span>{ __( 'Preview' ) }</span>
					</button>
				</div>
			</BlockControls>
			{ preview ? (
				<SandBox html={ attributes.html } />
			) : (
				<CodeEditor
					settings={ settings }
					value={ attributes.markdown }
					focus={ isSelected }
					onFocus={ toggleSelection }
					onChange={ ( content ) => setAttributes(
						{
							html: md.render( content ),
							markdown: content,
						}
					)
					}
				/>
			) }
		</div>
	) ),

	save( props, className ) {
		const {
			html,
			markdown,
		} = props.attributes;
		return <RawHTML className={ className } data-markdown={ markdown }>{ html }</RawHTML>;
	},

} );
