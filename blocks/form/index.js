( function ( blocks, element, blockEditor, i18n ) {
    var el = element.createElement;
    var __ = i18n.__;
    var useBlockProps = blockEditor.useBlockProps;

    blocks.registerBlockType( 'agocontact/form', {
        edit: function () {
            return el(
                'div',
                useBlockProps( { className: 'ago-contact-block-editor' } ),
                el(
                    'div',
                    { style: { padding: '24px', border: '1px dashed #c3c4c7', borderRadius: '6px', textAlign: 'center', color: '#50575e', background: '#fff' } },
                    el( 'span', { className: 'dashicons dashicons-email-alt', style: { fontSize: '28px', width: '28px', height: '28px', display: 'block', margin: '0 auto 8px' } } ),
                    el( 'strong', null, __( 'aGo Contact Form', 'ago-contact' ) ),
                    el( 'div', { style: { fontSize: '12px', marginTop: '4px' } }, __( 'The form renders here on the published page.', 'ago-contact' ) )
                )
            );
        },
        save: function () {
            return null;
        }
    } );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.i18n );
