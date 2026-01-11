/**
 * Vanilla JS Lexical Editor
 * A rich text editor without React dependencies
 */
import {
    createEditor,
    $getRoot,
    $getSelection,
    $isRangeSelection,
    $createParagraphNode,
    $createTextNode,
    $insertNodes,
    COMMAND_PRIORITY_LOW,
    COMMAND_PRIORITY_HIGH,
    KEY_ENTER_COMMAND,
    PASTE_COMMAND,
    CAN_UNDO_COMMAND,
    CAN_REDO_COMMAND,
} from 'lexical';
import { registerRichText, HeadingNode, QuoteNode } from '@lexical/rich-text';
import { registerHistory, createEmptyHistoryState } from '@lexical/history';
import { $generateHtmlFromNodes, $generateNodesFromDOM } from '@lexical/html';
import { ListNode, ListItemNode, registerList } from '@lexical/list';
import { CodeNode, CodeHighlightNode, registerCodeHighlighting } from '@lexical/code';
import { LinkNode, AutoLinkNode, $createLinkNode, $isLinkNode } from '@lexical/link';
import { TableNode, TableRowNode, TableCellNode } from '@lexical/table';
import { mergeRegister, $getNearestNodeOfType } from '@lexical/utils';

import { editorTheme } from './theme.js';
import { LexicalToolbar } from './toolbar.js';

export class LexicalEditor {
    constructor(containerElement, options = {}) {
        this.container = containerElement;
        this.options = {
            readOnly: false,
            placeholder: 'Start writing...',
            onSave: null,
            onContentChange: null,
            initialContent: '',
            ...options
        };

        this.editor = null;
        this.toolbar = null;
        this.contentElement = null;
        this.placeholderElement = null;
        this.cleanupFns = [];

        this.init();
    }

    init() {
        // Create editor structure
        this.createEditorDOM();

        // Initialize Lexical editor
        const config = {
            namespace: 'DocumentEditor',
            theme: editorTheme,
            editable: !this.options.readOnly,
            onError: (error) => {
                console.error('Lexical Error:', error);
            },
            nodes: [
                HeadingNode,
                QuoteNode,
                ListNode,
                ListItemNode,
                CodeNode,
                CodeHighlightNode,
                LinkNode,
                AutoLinkNode,
                TableNode,
                TableRowNode,
                TableCellNode,
            ],
        };

        this.editor = createEditor(config);
        this.editor.setRootElement(this.contentElement);

        // Register plugins
        this.registerPlugins();

        // Create toolbar
        if (!this.options.readOnly) {
            this.toolbar = new LexicalToolbar(this.editor, this.container);
        }

        // Set initial content
        if (this.options.initialContent) {
            this.setContent(this.options.initialContent);
        }

        // Register listeners
        this.registerListeners();

        // Update placeholder visibility
        this.updatePlaceholder();
    }

    createEditorDOM() {
        this.container.innerHTML = '';
        this.container.className = 'lexical-editor-container';

        // Create content editable area
        this.contentElement = document.createElement('div');
        this.contentElement.className = 'lexical-content';
        this.contentElement.contentEditable = !this.options.readOnly ? 'true' : 'false';

        // Create placeholder
        this.placeholderElement = document.createElement('div');
        this.placeholderElement.className = 'lexical-placeholder';
        this.placeholderElement.textContent = this.options.placeholder;

        // Wrapper for content and placeholder
        const editorWrapper = document.createElement('div');
        editorWrapper.className = 'lexical-editor-wrapper';
        editorWrapper.appendChild(this.placeholderElement);
        editorWrapper.appendChild(this.contentElement);

        this.container.appendChild(editorWrapper);
    }

    registerPlugins() {
        // Rich text plugin
        this.cleanupFns.push(registerRichText(this.editor));

        // History plugin (undo/redo)
        this.cleanupFns.push(
            registerHistory(this.editor, createEmptyHistoryState(), 1000)
        );

        // List plugin
        this.cleanupFns.push(registerList(this.editor));

        // Code highlighting
        this.cleanupFns.push(registerCodeHighlighting(this.editor));

        // Custom link handling
        this.cleanupFns.push(
            this.editor.registerCommand(
                PASTE_COMMAND,
                (event) => {
                    const clipboardData = event instanceof ClipboardEvent ? event.clipboardData : null;
                    if (clipboardData) {
                        const text = clipboardData.getData('text/plain');
                        // Check if pasted text is a URL
                        if (this.isValidUrl(text)) {
                            this.editor.update(() => {
                                const selection = $getSelection();
                                if ($isRangeSelection(selection)) {
                                    const linkNode = $createLinkNode(text);
                                    linkNode.append($createTextNode(text));
                                    selection.insertNodes([linkNode]);
                                }
                            });
                            return true;
                        }
                    }
                    return false;
                },
                COMMAND_PRIORITY_LOW
            )
        );
    }

    registerListeners() {
        // Content change listener
        this.cleanupFns.push(
            this.editor.registerUpdateListener(({ editorState, dirtyElements, dirtyLeaves }) => {
                // Update placeholder
                this.updatePlaceholder();

                // Trigger content change callback
                if (dirtyElements.size > 0 || dirtyLeaves.size > 0) {
                    if (this.options.onContentChange) {
                        this.options.onContentChange(this.getHtml());
                    }
                }
            })
        );

        // Can undo/redo listeners
        this.cleanupFns.push(
            this.editor.registerCommand(
                CAN_UNDO_COMMAND,
                (canUndo) => {
                    if (this.toolbar) {
                        this.toolbar.setCanUndo(canUndo);
                    }
                    return false;
                },
                COMMAND_PRIORITY_LOW
            ),
            this.editor.registerCommand(
                CAN_REDO_COMMAND,
                (canRedo) => {
                    if (this.toolbar) {
                        this.toolbar.setCanRedo(canRedo);
                    }
                    return false;
                },
                COMMAND_PRIORITY_LOW
            )
        );

        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleKeyDown.bind(this));
    }

    handleKeyDown(event) {
        // Save shortcut (Ctrl+S / Cmd+S)
        if ((event.ctrlKey || event.metaKey) && event.key === 's') {
            event.preventDefault();
            if (this.options.onSave) {
                this.options.onSave(this.getHtml());
            }
        }
    }

    updatePlaceholder() {
        this.editor.getEditorState().read(() => {
            const root = $getRoot();
            const children = root.getChildren();
            const isEmpty = children.length === 0 ||
                (children.length === 1 && children[0].getTextContent() === '');

            this.placeholderElement.style.display = isEmpty ? 'block' : 'none';
        });
    }

    isValidUrl(string) {
        try {
            const url = new URL(string);
            return url.protocol === 'http:' || url.protocol === 'https:';
        } catch {
            return false;
        }
    }

    /**
     * Set editor content from HTML string
     */
    setContent(html) {
        this.editor.update(() => {
            const root = $getRoot();
            root.clear();

            if (html && html.trim()) {
                const parser = new DOMParser();
                const dom = parser.parseFromString(html, 'text/html');
                const nodes = $generateNodesFromDOM(this.editor, dom);

                if (nodes.length > 0) {
                    root.append(...nodes);
                } else {
                    const paragraph = $createParagraphNode();
                    root.append(paragraph);
                }
            } else {
                const paragraph = $createParagraphNode();
                root.append(paragraph);
            }
        });
    }

    /**
     * Get editor content as HTML string
     */
    getHtml() {
        let html = '';
        this.editor.getEditorState().read(() => {
            html = $generateHtmlFromNodes(this.editor, null);
        });
        return html;
    }

    /**
     * Get plain text content
     */
    getText() {
        let text = '';
        this.editor.getEditorState().read(() => {
            text = $getRoot().getTextContent();
        });
        return text;
    }

    /**
     * Set read-only mode
     */
    setReadOnly(readOnly) {
        this.options.readOnly = readOnly;
        this.editor.setEditable(!readOnly);
        this.contentElement.contentEditable = !readOnly ? 'true' : 'false';

        if (this.toolbar) {
            this.toolbar.setReadOnly(readOnly);
        }

        this.container.classList.toggle('read-only', readOnly);
    }

    /**
     * Focus the editor
     */
    focus() {
        this.editor.focus();
    }

    /**
     * Blur the editor
     */
    blur() {
        this.contentElement.blur();
    }

    /**
     * Insert image at current cursor position
     */
    insertImage(url, alt = '') {
        this.editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                // For now, insert as a link to image (full image node would require custom node)
                const imageHtml = `<img src="${url}" alt="${alt}" />`;
                // You could implement a custom ImageNode here
            }
        });
    }

    /**
     * Get current selection info (for comments feature)
     */
    getSelectionInfo() {
        let info = null;
        this.editor.getEditorState().read(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                const text = selection.getTextContent();
                const anchor = selection.anchor;
                const focus = selection.focus;
                info = {
                    text: text,
                    start: Math.min(anchor.offset, focus.offset),
                    end: Math.max(anchor.offset, focus.offset),
                    isEmpty: selection.isCollapsed(),
                };
            }
        });
        return info;
    }

    /**
     * Clean up editor
     */
    destroy() {
        document.removeEventListener('keydown', this.handleKeyDown.bind(this));
        this.cleanupFns.forEach(fn => fn());
        this.cleanupFns = [];
        this.container.innerHTML = '';
    }
}

// Export for use without ES modules
if (typeof window !== 'undefined') {
    window.LexicalEditor = LexicalEditor;
}

export default LexicalEditor;
