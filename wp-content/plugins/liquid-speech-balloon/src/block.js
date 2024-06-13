const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { __ } = wp.i18n;
const { PanelBody, SelectControl } = wp.components;
const {
    RichText,
    BlockControls,
    AlignmentToolbar,
    InspectorControls,
    PanelColorSettings,
} = wp.editor;

registerBlockType( 'liquid/speech-balloon', {
    title: __( 'Speech Balloon', 'liquid-speech-balloon' ),
    icon: 'admin-comments',
    description: __( 'Talk style design. Speech bubble' ),
    keywords: [ 'talk', 'speech', 'bubble', '吹き出し', 'フキダシ' ],
    category: 'common',
    example: {},
    attributes: {
        content: {
            type: 'string',
            source: 'html',
            selector: 'p',
        },
        alignment: {
            type: 'string',
        },
        bgColor: {
            type: 'string',
        },
        txColor: {
            type: 'string',
        },
		avatar: {
			type: 'string',
			default: 'liquid-speech-balloon-00',
		},
		direction: {
			type: 'string',
			default: '',
		},
		design: {
			type: 'string',
			default: '',
		},
		size: {
			type: 'string',
			default: '',
		},
		option: {
			type: 'string',
			default: '',
		},
    },

    edit( { attributes, className, setAttributes } ) {
        const { content, alignment, bgColor, txColor, avatar, direction, design, size, option } = attributes;

        function onChangeContent( newContent ) {
            setAttributes( { content: newContent } );
        }

        function onChangeAlignment( newAlignment ) {
            setAttributes( { alignment: newAlignment } );
        }

        var buildOptions = [];
        if( liquid_speech_balloon_name ){
            for ( let i = 0; i < Object.keys(liquid_speech_balloon_name).length; i++ ) {
                if ( i < 100 ){
                    var num = ("0" + i).slice(-2);
                } else {
                    var num = i;
                }
                if ( i == 0 ){
                    var names = liquid_speech_balloon_name[num] ? __( 'Default', 'liquid-speech-balloon' )+': '+liquid_speech_balloon_name[num] : __( 'Default', 'liquid-speech-balloon' );
                }else{
                    var names = liquid_speech_balloon_name[num] ? num+': '+liquid_speech_balloon_name[num] : num;
                }
                if( liquid_speech_balloon_note ){
                    var notes = liquid_speech_balloon_note[num] ? ' ('+liquid_speech_balloon_note[num]+')' : '';
                }else{
                    var notes = '';
                }
                var buildOptionAdd = [{
                    value: 'liquid-speech-balloon-'+num,
                    label: names + notes,
                }];
                buildOptions.push(...buildOptionAdd);
            }
        }

        if( bgColor && option == "liquid-speech-balloon-vertical" ){
            var bgColors = bgColor + " transparent transparent transparent";
        }else if( bgColor && option == "liquid-speech-balloon-vertical-reverse" ){
            var bgColors = "transparent transparent " + bgColor + " transparent";
        }else if( bgColor && direction == "liquid-speech-balloon-right" ){
            var bgColors = "transparent transparent transparent " + bgColor;
        }else if( bgColor ){
            var bgColors = "transparent " + bgColor + " transparent transparent";
        }

        return (
            <Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Speech Settings', 'liquid-speech-balloon' ) }>
						<SelectControl
							label={ __( 'Avatar', 'liquid-speech-balloon' ) }
							value={ avatar }
							onChange={ ( value ) => setAttributes( { avatar: value } ) }
							options={ buildOptions }
						/>
						<SelectControl
							label={ __( 'Direction', 'liquid-speech-balloon' ) }
							value={ direction }
							onChange={ ( value ) => setAttributes( { direction: value } ) }
							options={ [
								{
									value: '',
									label: __( 'Left', 'liquid-speech-balloon' ),
								},
								{
									value: 'liquid-speech-balloon-right',
									label: __( 'Right', 'liquid-speech-balloon' ),
								},
							] }
						/>
						<SelectControl
							label={ __( 'Design', 'liquid-speech-balloon' ) }
							value={ design }
							onChange={ ( value ) => setAttributes( { design: value } ) }
							options={ [
								{
									value: '',
									label: __( 'Default', 'liquid-speech-balloon' ),
								},
								{
									value: 'liquid-speech-balloon-bubble',
									label: __( 'Bubble', 'liquid-speech-balloon' ),
								},
								{
									value: 'liquid-speech-balloon-square',
									label: __( 'Square', 'liquid-speech-balloon' ),
								},
								{
									value: 'liquid-speech-balloon-dashed',
									label: __( 'Dashed', 'liquid-speech-balloon' ),
								},
								{
									value: 'liquid-speech-balloon-shadow',
									label: __( 'Shadow', 'liquid-speech-balloon' ),
								},
								{
									value: 'liquid-speech-balloon-borderless',
									label: __( 'Borderless', 'liquid-speech-balloon' ),
								},
							] }
						/>
						<SelectControl
							label={ __( 'Size', 'liquid-speech-balloon' ) }
							value={ size }
							onChange={ ( value ) => setAttributes( { size: value } ) }
							options={ [
								{
									value: '',
									label: __( 'Default', 'liquid-speech-balloon' ),
								},
								{
									value: 'liquid-speech-balloon-small',
									label: __( 'Small', 'liquid-speech-balloon' ),
								},
								{
									value: 'liquid-speech-balloon-large',
									label: __( 'Large', 'liquid-speech-balloon' ),
								},
							] }
						/>
						<SelectControl
							label={ __( 'Options', 'liquid-speech-balloon' ) }
							value={ option }
							onChange={ ( value ) => setAttributes( { option: value } ) }
							options={ [
								{
									value: '',
									label: __( 'Default', 'liquid-speech-balloon' ),
								},
								{
									value: 'liquid-speech-balloon-short',
									label: __( 'Short', 'liquid-speech-balloon' ),
								},
								{
									value: 'liquid-speech-balloon-vertical',
									label: __( 'Vertical', 'liquid-speech-balloon' ),
								},
								{
									value: 'liquid-speech-balloon-vertical-reverse',
									label: __( 'Vertical Reverse', 'liquid-speech-balloon' ),
								},
							] }
						/>
					    <p><a href="options-general.php?page=liquid-speech-balloon" target="_blank">{ __( 'Avatar and name settings', 'liquid-speech-balloon' ) }</a><br/><a href="https://lqd.jp/wp/?utm_source=admin&utm_medium=plugin&utm_campaign=speech" target="_blank">LIQUID PRESS</a></p>
					</PanelBody>
                    <PanelColorSettings
                            title={ __( 'Color Settings' ) }
                            colorSettings={ [
                                {
                                    value: bgColor,
                                    onChange: ( value ) => setAttributes( { bgColor: value } ),
                                    label: __( 'Background Color' ),
                                },
                                {
                                    value: txColor,
                                    onChange: ( value ) => setAttributes( { txColor: value } ),
                                    label: __( 'Text Color' ),
                                },
                            ] }
                        >
                    </PanelColorSettings>
				</InspectorControls>
                <BlockControls>
                    <AlignmentToolbar
                        value={ alignment }
                        onChange={ onChangeAlignment }
                    />
                </BlockControls>
                <div className={ 'liquid-speech-balloon-wrap' + ' ' + avatar + ' ' + direction + ' ' + design + ' ' + size + ' ' + option }>
                    <div className="liquid-speech-balloon-avatar"></div>
                    <div className="liquid-speech-balloon-text"
                            style={ { borderColor: bgColor, backgroundColor: bgColor, color: txColor } }>
                        <RichText
                            key="editable"
                            tagName="p"
                            placeholder={ __( 'Speech...', 'liquid-speech-balloon' ) }
                            className={ className }
                            style={ { textAlign: alignment, backgroundColor: bgColor, color: txColor } }
                            onChange={ onChangeContent }
                            value={ content }
                        />
                        <div
                            className="liquid-speech-balloon-arrow"
                            style={ { borderColor: bgColors } }>
                        </div>
                    </div>
                </div>
            </Fragment>
        );
    },

    save( { attributes } ) {
        const { content, alignment, bgColor, txColor, avatar, direction, design, size, option } = attributes;

        if( bgColor && option == "liquid-speech-balloon-vertical" ){
            var bgColors = bgColor + " transparent transparent transparent";
        }else if( bgColor && option == "liquid-speech-balloon-vertical-reverse" ){
            var bgColors = "transparent transparent " + bgColor + " transparent";
        }else if( bgColor && direction == "liquid-speech-balloon-right" ){
            var bgColors = "transparent transparent transparent " + bgColor;
        }else if( bgColor ){
            var bgColors = "transparent " + bgColor + " transparent transparent";
        }

        return (
            <div className={ 'liquid-speech-balloon-wrap' + ' ' + avatar + ' ' + direction + ' ' + design + ' ' + size + ' ' + option }>
                <div className="liquid-speech-balloon-avatar"></div>
                <div className="liquid-speech-balloon-text"
                        style={ { borderColor: bgColor, backgroundColor: bgColor, color: txColor } }>
                    <RichText.Content
                        style={ { textAlign: alignment } }
                        value={ content }
                        tagName="p"
                    />
                    <div
                        className="liquid-speech-balloon-arrow"
                        style={ { borderColor: bgColors } }>
                    </div>
                </div>
            </div>
        );
    },
} );