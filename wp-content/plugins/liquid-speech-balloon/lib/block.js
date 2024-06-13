'use strict';

var registerBlockType = wp.blocks.registerBlockType;
var Fragment = wp.element.Fragment;
var __ = wp.i18n.__;
var _wp$components = wp.components,
    PanelBody = _wp$components.PanelBody,
    SelectControl = _wp$components.SelectControl;
var _wp$editor = wp.editor,
    RichText = _wp$editor.RichText,
    BlockControls = _wp$editor.BlockControls,
    AlignmentToolbar = _wp$editor.AlignmentToolbar,
    InspectorControls = _wp$editor.InspectorControls,
    PanelColorSettings = _wp$editor.PanelColorSettings;


registerBlockType('liquid/speech-balloon', {
    title: __('Speech Balloon', 'liquid-speech-balloon'),
    icon: 'admin-comments',
    description: __('Talk style design. Speech bubble'),
    keywords: ['talk', 'speech', 'bubble', '吹き出し', 'フキダシ'],
    category: 'common',
    example: {},
    attributes: {
        content: {
            type: 'string',
            source: 'html',
            selector: 'p'
        },
        alignment: {
            type: 'string'
        },
        bgColor: {
            type: 'string'
        },
        txColor: {
            type: 'string'
        },
        avatar: {
            type: 'string',
            default: 'liquid-speech-balloon-00'
        },
        direction: {
            type: 'string',
            default: ''
        },
        design: {
            type: 'string',
            default: ''
        },
        size: {
            type: 'string',
            default: ''
        },
        option: {
            type: 'string',
            default: ''
        }
    },

    edit: function edit(_ref) {
        var attributes = _ref.attributes,
            className = _ref.className,
            setAttributes = _ref.setAttributes;
        var content = attributes.content,
            alignment = attributes.alignment,
            bgColor = attributes.bgColor,
            txColor = attributes.txColor,
            avatar = attributes.avatar,
            direction = attributes.direction,
            design = attributes.design,
            size = attributes.size,
            option = attributes.option;


        function onChangeContent(newContent) {
            setAttributes({ content: newContent });
        }

        function onChangeAlignment(newAlignment) {
            setAttributes({ alignment: newAlignment });
        }

        var buildOptions = [];
        if (liquid_speech_balloon_name) {
            for (var i = 0; i < Object.keys(liquid_speech_balloon_name).length; i++) {
                if (i < 100) {
                    var num = ("0" + i).slice(-2);
                } else {
                    var num = i;
                }
                if (i == 0) {
                    var names = liquid_speech_balloon_name[num] ? __('Default', 'liquid-speech-balloon') + ': ' + liquid_speech_balloon_name[num] : __('Default', 'liquid-speech-balloon');
                } else {
                    var names = liquid_speech_balloon_name[num] ? num + ': ' + liquid_speech_balloon_name[num] : num;
                }
                if (liquid_speech_balloon_note) {
                    var notes = liquid_speech_balloon_note[num] ? ' (' + liquid_speech_balloon_note[num] + ')' : '';
                } else {
                    var notes = '';
                }
                var buildOptionAdd = [{
                    value: 'liquid-speech-balloon-' + num,
                    label: names + notes
                }];
                buildOptions.push.apply(buildOptions, buildOptionAdd);
            }
        }

        if (bgColor && option == "liquid-speech-balloon-vertical") {
            var bgColors = bgColor + " transparent transparent transparent";
        } else if (bgColor && option == "liquid-speech-balloon-vertical-reverse") {
            var bgColors = "transparent transparent " + bgColor + " transparent";
        } else if (bgColor && direction == "liquid-speech-balloon-right") {
            var bgColors = "transparent transparent transparent " + bgColor;
        } else if (bgColor) {
            var bgColors = "transparent " + bgColor + " transparent transparent";
        }

        return React.createElement(
            Fragment,
            null,
            React.createElement(
                InspectorControls,
                null,
                React.createElement(
                    PanelBody,
                    { title: __('Speech Settings', 'liquid-speech-balloon') },
                    React.createElement(SelectControl, {
                        label: __('Avatar', 'liquid-speech-balloon'),
                        value: avatar,
                        onChange: function onChange(value) {
                            return setAttributes({ avatar: value });
                        },
                        options: buildOptions
                    }),
                    React.createElement(SelectControl, {
                        label: __('Direction', 'liquid-speech-balloon'),
                        value: direction,
                        onChange: function onChange(value) {
                            return setAttributes({ direction: value });
                        },
                        options: [{
                            value: '',
                            label: __('Left', 'liquid-speech-balloon')
                        }, {
                            value: 'liquid-speech-balloon-right',
                            label: __('Right', 'liquid-speech-balloon')
                        }]
                    }),
                    React.createElement(SelectControl, {
                        label: __('Design', 'liquid-speech-balloon'),
                        value: design,
                        onChange: function onChange(value) {
                            return setAttributes({ design: value });
                        },
                        options: [{
                            value: '',
                            label: __('Default', 'liquid-speech-balloon')
                        }, {
                            value: 'liquid-speech-balloon-bubble',
                            label: __('Bubble', 'liquid-speech-balloon')
                        }, {
                            value: 'liquid-speech-balloon-square',
                            label: __('Square', 'liquid-speech-balloon')
                        }, {
                            value: 'liquid-speech-balloon-dashed',
                            label: __('Dashed', 'liquid-speech-balloon')
                        }, {
                            value: 'liquid-speech-balloon-shadow',
                            label: __('Shadow', 'liquid-speech-balloon')
                        }, {
                            value: 'liquid-speech-balloon-borderless',
                            label: __('Borderless', 'liquid-speech-balloon')
                        }]
                    }),
                    React.createElement(SelectControl, {
                        label: __('Size', 'liquid-speech-balloon'),
                        value: size,
                        onChange: function onChange(value) {
                            return setAttributes({ size: value });
                        },
                        options: [{
                            value: '',
                            label: __('Default', 'liquid-speech-balloon')
                        }, {
                            value: 'liquid-speech-balloon-small',
                            label: __('Small', 'liquid-speech-balloon')
                        }, {
                            value: 'liquid-speech-balloon-large',
                            label: __('Large', 'liquid-speech-balloon')
                        }]
                    }),
                    React.createElement(SelectControl, {
                        label: __('Options', 'liquid-speech-balloon'),
                        value: option,
                        onChange: function onChange(value) {
                            return setAttributes({ option: value });
                        },
                        options: [{
                            value: '',
                            label: __('Default', 'liquid-speech-balloon')
                        }, {
                            value: 'liquid-speech-balloon-short',
                            label: __('Short', 'liquid-speech-balloon')
                        }, {
                            value: 'liquid-speech-balloon-vertical',
                            label: __('Vertical', 'liquid-speech-balloon')
                        }, {
                            value: 'liquid-speech-balloon-vertical-reverse',
                            label: __('Vertical Reverse', 'liquid-speech-balloon')
                        }]
                    }),
                    React.createElement(
                        'p',
                        null,
                        React.createElement(
                            'a',
                            { href: 'options-general.php?page=liquid-speech-balloon', target: '_blank' },
                            __('Avatar and name settings', 'liquid-speech-balloon')
                        ),
                        React.createElement('br', null),
                        React.createElement(
                            'a',
                            { href: 'https://lqd.jp/wp/?utm_source=admin&utm_medium=plugin&utm_campaign=speech', target: '_blank' },
                            'LIQUID PRESS'
                        )
                    )
                ),
                React.createElement(PanelColorSettings, {
                    title: __('Color Settings'),
                    colorSettings: [{
                        value: bgColor,
                        onChange: function onChange(value) {
                            return setAttributes({ bgColor: value });
                        },
                        label: __('Background Color')
                    }, {
                        value: txColor,
                        onChange: function onChange(value) {
                            return setAttributes({ txColor: value });
                        },
                        label: __('Text Color')
                    }]
                })
            ),
            React.createElement(
                BlockControls,
                null,
                React.createElement(AlignmentToolbar, {
                    value: alignment,
                    onChange: onChangeAlignment
                })
            ),
            React.createElement(
                'div',
                { className: 'liquid-speech-balloon-wrap' + ' ' + avatar + ' ' + direction + ' ' + design + ' ' + size + ' ' + option },
                React.createElement('div', { className: 'liquid-speech-balloon-avatar' }),
                React.createElement(
                    'div',
                    { className: 'liquid-speech-balloon-text',
                        style: { borderColor: bgColor, backgroundColor: bgColor, color: txColor } },
                    React.createElement(RichText, {
                        key: 'editable',
                        tagName: 'p',
                        placeholder: __('Speech...', 'liquid-speech-balloon'),
                        className: className,
                        style: { textAlign: alignment, backgroundColor: bgColor, color: txColor },
                        onChange: onChangeContent,
                        value: content
                    }),
                    React.createElement('div', {
                        className: 'liquid-speech-balloon-arrow',
                        style: { borderColor: bgColors } })
                )
            )
        );
    },
    save: function save(_ref2) {
        var attributes = _ref2.attributes;
        var content = attributes.content,
            alignment = attributes.alignment,
            bgColor = attributes.bgColor,
            txColor = attributes.txColor,
            avatar = attributes.avatar,
            direction = attributes.direction,
            design = attributes.design,
            size = attributes.size,
            option = attributes.option;


        if (bgColor && option == "liquid-speech-balloon-vertical") {
            var bgColors = bgColor + " transparent transparent transparent";
        } else if (bgColor && option == "liquid-speech-balloon-vertical-reverse") {
            var bgColors = "transparent transparent " + bgColor + " transparent";
        } else if (bgColor && direction == "liquid-speech-balloon-right") {
            var bgColors = "transparent transparent transparent " + bgColor;
        } else if (bgColor) {
            var bgColors = "transparent " + bgColor + " transparent transparent";
        }

        return React.createElement(
            'div',
            { className: 'liquid-speech-balloon-wrap' + ' ' + avatar + ' ' + direction + ' ' + design + ' ' + size + ' ' + option },
            React.createElement('div', { className: 'liquid-speech-balloon-avatar' }),
            React.createElement(
                'div',
                { className: 'liquid-speech-balloon-text',
                    style: { borderColor: bgColor, backgroundColor: bgColor, color: txColor } },
                React.createElement(RichText.Content, {
                    style: { textAlign: alignment },
                    value: content,
                    tagName: 'p'
                }),
                React.createElement('div', {
                    className: 'liquid-speech-balloon-arrow',
                    style: { borderColor: bgColors } })
            )
        );
    }
});