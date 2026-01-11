/**
 * Lexical Editor Toolbar
 * Creates and manages the formatting toolbar
 */
import {
    $getSelection,
    $isRangeSelection,
    FORMAT_TEXT_COMMAND,
    FORMAT_ELEMENT_COMMAND,
    UNDO_COMMAND,
    REDO_COMMAND,
    INDENT_CONTENT_COMMAND,
    OUTDENT_CONTENT_COMMAND,
    COMMAND_PRIORITY_CRITICAL,
    $createParagraphNode,
    $createTextNode,
    $isRootOrShadowRoot,
    $getNodeByKey,
} from 'lexical';
import { $setBlocksType } from '@lexical/selection';
import { $createHeadingNode, $createQuoteNode, $isHeadingNode } from '@lexical/rich-text';
import { INSERT_ORDERED_LIST_COMMAND, INSERT_UNORDERED_LIST_COMMAND, INSERT_CHECK_LIST_COMMAND, REMOVE_LIST_COMMAND, $isListNode } from '@lexical/list';
import { $createCodeNode, $isCodeNode } from '@lexical/code';
import { $isLinkNode, TOGGLE_LINK_COMMAND } from '@lexical/link';
import { $findMatchingParent, $getNearestNodeOfType, mergeRegister } from '@lexical/utils';

export class LexicalToolbar {
    constructor(editor, containerElement) {
        this.editor = editor;
        this.container = containerElement;
        this.activeFormats = new Set();
        this.currentBlockType = 'paragraph';
        this.canUndo = false;
        this.canRedo = false;

        this.createToolbar();
        this.registerListeners();
    }

    createToolbar() {
        this.toolbarElement = document.createElement('div');
        this.toolbarElement.className = 'lexical-toolbar';
        this.toolbarElement.innerHTML = `
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-action="undo" title="Undo (Ctrl+Z)" disabled>
                    <span class="icon-[tabler--arrow-back-up] size-4"></span>
                </button>
                <button type="button" class="toolbar-btn" data-action="redo" title="Redo (Ctrl+Shift+Z)" disabled>
                    <span class="icon-[tabler--arrow-forward-up] size-4"></span>
                </button>
            </div>
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <div class="toolbar-dropdown" data-dropdown="block-type">
                    <button type="button" class="toolbar-dropdown-btn">
                        <span class="block-icon icon-[tabler--pilcrow] size-4"></span>
                        <span class="dropdown-label">Normal</span>
                        <span class="icon-[tabler--chevron-down] size-3.5"></span>
                    </button>
                    <div class="toolbar-dropdown-menu">
                        <button type="button" class="dropdown-item" data-block="paragraph">
                            <span class="icon-[tabler--pilcrow] size-4"></span>
                            <span>Normal</span>
                        </button>
                        <button type="button" class="dropdown-item" data-block="h1">
                            <span class="icon-[tabler--h-1] size-4"></span>
                            <span>Heading 1</span>
                        </button>
                        <button type="button" class="dropdown-item" data-block="h2">
                            <span class="icon-[tabler--h-2] size-4"></span>
                            <span>Heading 2</span>
                        </button>
                        <button type="button" class="dropdown-item" data-block="h3">
                            <span class="icon-[tabler--h-3] size-4"></span>
                            <span>Heading 3</span>
                        </button>
                        <button type="button" class="dropdown-item" data-block="h4">
                            <span class="icon-[tabler--h-4] size-4"></span>
                            <span>Heading 4</span>
                        </button>
                        <button type="button" class="dropdown-item" data-block="h5">
                            <span class="icon-[tabler--h-5] size-4"></span>
                            <span>Heading 5</span>
                        </button>
                        <button type="button" class="dropdown-item" data-block="h6">
                            <span class="icon-[tabler--h-6] size-4"></span>
                            <span>Heading 6</span>
                        </button>
                        <div class="dropdown-divider"></div>
                        <button type="button" class="dropdown-item" data-block="bullet">
                            <span class="icon-[tabler--list] size-4"></span>
                            <span>Bullet List</span>
                        </button>
                        <button type="button" class="dropdown-item" data-block="number">
                            <span class="icon-[tabler--list-numbers] size-4"></span>
                            <span>Numbered List</span>
                        </button>
                        <button type="button" class="dropdown-item" data-block="check">
                            <span class="icon-[tabler--list-check] size-4"></span>
                            <span>Check List</span>
                        </button>
                        <div class="dropdown-divider"></div>
                        <button type="button" class="dropdown-item" data-block="quote">
                            <span class="icon-[tabler--quote] size-4"></span>
                            <span>Quote</span>
                        </button>
                        <button type="button" class="dropdown-item" data-block="code">
                            <span class="icon-[tabler--code] size-4"></span>
                            <span>Code Block</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <div class="toolbar-dropdown" data-dropdown="font-family">
                    <button type="button" class="toolbar-dropdown-btn">
                        <span class="dropdown-label font-label">Arial</span>
                        <span class="icon-[tabler--chevron-down] size-3.5"></span>
                    </button>
                    <div class="toolbar-dropdown-menu font-menu">
                        <button type="button" class="dropdown-item active" data-font="Arial" style="font-family: Arial">Arial</button>
                        <button type="button" class="dropdown-item" data-font="Georgia" style="font-family: Georgia">Georgia</button>
                        <button type="button" class="dropdown-item" data-font="Times New Roman" style="font-family: 'Times New Roman'">Times New Roman</button>
                        <button type="button" class="dropdown-item" data-font="Courier New" style="font-family: 'Courier New'">Courier New</button>
                        <button type="button" class="dropdown-item" data-font="Verdana" style="font-family: Verdana">Verdana</button>
                    </div>
                </div>
            </div>
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <div class="toolbar-dropdown" data-dropdown="font-size">
                    <button type="button" class="toolbar-dropdown-btn">
                        <span class="dropdown-label size-label">16px</span>
                        <span class="icon-[tabler--chevron-down] size-3.5"></span>
                    </button>
                    <div class="toolbar-dropdown-menu size-menu">
                        <button type="button" class="dropdown-item" data-size="12">12px</button>
                        <button type="button" class="dropdown-item" data-size="14">14px</button>
                        <button type="button" class="dropdown-item active" data-size="16">16px</button>
                        <button type="button" class="dropdown-item" data-size="18">18px</button>
                        <button type="button" class="dropdown-item" data-size="20">20px</button>
                        <button type="button" class="dropdown-item" data-size="24">24px</button>
                        <button type="button" class="dropdown-item" data-size="30">30px</button>
                    </div>
                </div>
            </div>
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-format="bold" title="Bold (Ctrl+B)">
                    <span class="icon-[tabler--bold] size-4"></span>
                </button>
                <button type="button" class="toolbar-btn" data-format="italic" title="Italic (Ctrl+I)">
                    <span class="icon-[tabler--italic] size-4"></span>
                </button>
                <button type="button" class="toolbar-btn" data-format="underline" title="Underline (Ctrl+U)">
                    <span class="icon-[tabler--underline] size-4"></span>
                </button>
                <button type="button" class="toolbar-btn" data-format="strikethrough" title="Strikethrough">
                    <span class="icon-[tabler--strikethrough] size-4"></span>
                </button>
                <button type="button" class="toolbar-btn" data-format="code" title="Inline Code">
                    <span class="icon-[tabler--code] size-4"></span>
                </button>
                <button type="button" class="toolbar-btn" data-format="subscript" title="Subscript">
                    <span class="icon-[tabler--subscript] size-4"></span>
                </button>
                <button type="button" class="toolbar-btn" data-format="superscript" title="Superscript">
                    <span class="icon-[tabler--superscript] size-4"></span>
                </button>
                <button type="button" class="toolbar-btn" data-action="clear-format" title="Clear Formatting">
                    <span class="icon-[tabler--clear-formatting] size-4"></span>
                </button>
            </div>
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-action="link" title="Insert Link">
                    <span class="icon-[tabler--link] size-4"></span>
                </button>
                <div class="toolbar-dropdown" data-dropdown="text-color">
                    <button type="button" class="toolbar-dropdown-btn color-btn" title="Text Color">
                        <span class="icon-[tabler--letter-a] size-4"></span>
                        <span class="color-indicator" style="background: #000000"></span>
                    </button>
                    <div class="toolbar-dropdown-menu color-menu">
                        <div class="color-grid">
                            <button type="button" class="color-item" data-color="#000000" style="background: #000000" title="Black"></button>
                            <button type="button" class="color-item" data-color="#5c5c5c" style="background: #5c5c5c" title="Dark Gray"></button>
                            <button type="button" class="color-item" data-color="#999999" style="background: #999999" title="Gray"></button>
                            <button type="button" class="color-item" data-color="#e74c3c" style="background: #e74c3c" title="Red"></button>
                            <button type="button" class="color-item" data-color="#e67e22" style="background: #e67e22" title="Orange"></button>
                            <button type="button" class="color-item" data-color="#f1c40f" style="background: #f1c40f" title="Yellow"></button>
                            <button type="button" class="color-item" data-color="#2ecc71" style="background: #2ecc71" title="Green"></button>
                            <button type="button" class="color-item" data-color="#3498db" style="background: #3498db" title="Blue"></button>
                            <button type="button" class="color-item" data-color="#9b59b6" style="background: #9b59b6" title="Purple"></button>
                            <button type="button" class="color-item" data-color="#1abc9c" style="background: #1abc9c" title="Teal"></button>
                        </div>
                    </div>
                </div>
                <div class="toolbar-dropdown" data-dropdown="bg-color">
                    <button type="button" class="toolbar-dropdown-btn color-btn" title="Background Color">
                        <span class="icon-[tabler--highlight] size-4"></span>
                        <span class="color-indicator bg-indicator" style="background: transparent; border: 1px dashed #ccc"></span>
                    </button>
                    <div class="toolbar-dropdown-menu color-menu">
                        <div class="color-grid">
                            <button type="button" class="color-item no-color" data-color="transparent" title="No Color">
                                <span class="icon-[tabler--x] size-3"></span>
                            </button>
                            <button type="button" class="color-item" data-color="#ffeaa7" style="background: #ffeaa7" title="Yellow"></button>
                            <button type="button" class="color-item" data-color="#81ecec" style="background: #81ecec" title="Cyan"></button>
                            <button type="button" class="color-item" data-color="#74b9ff" style="background: #74b9ff" title="Blue"></button>
                            <button type="button" class="color-item" data-color="#a29bfe" style="background: #a29bfe" title="Purple"></button>
                            <button type="button" class="color-item" data-color="#fd79a8" style="background: #fd79a8" title="Pink"></button>
                            <button type="button" class="color-item" data-color="#55efc4" style="background: #55efc4" title="Green"></button>
                            <button type="button" class="color-item" data-color="#fab1a0" style="background: #fab1a0" title="Peach"></button>
                            <button type="button" class="color-item" data-color="#dfe6e9" style="background: #dfe6e9" title="Light Gray"></button>
                            <button type="button" class="color-item" data-color="#ffefd5" style="background: #ffefd5" title="Papaya"></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <div class="toolbar-dropdown" data-dropdown="align">
                    <button type="button" class="toolbar-dropdown-btn">
                        <span class="align-icon icon-[tabler--align-left] size-4"></span>
                        <span class="icon-[tabler--chevron-down] size-3.5"></span>
                    </button>
                    <div class="toolbar-dropdown-menu">
                        <button type="button" class="dropdown-item active" data-align="left">
                            <span class="icon-[tabler--align-left] size-4"></span>
                            <span>Left Align</span>
                        </button>
                        <button type="button" class="dropdown-item" data-align="center">
                            <span class="icon-[tabler--align-center] size-4"></span>
                            <span>Center Align</span>
                        </button>
                        <button type="button" class="dropdown-item" data-align="right">
                            <span class="icon-[tabler--align-right] size-4"></span>
                            <span>Right Align</span>
                        </button>
                        <button type="button" class="dropdown-item" data-align="justify">
                            <span class="icon-[tabler--align-justified] size-4"></span>
                            <span>Justify</span>
                        </button>
                        <div class="dropdown-divider"></div>
                        <button type="button" class="dropdown-item" data-indent="outdent">
                            <span class="icon-[tabler--indent-decrease] size-4"></span>
                            <span>Outdent</span>
                        </button>
                        <button type="button" class="dropdown-item" data-indent="indent">
                            <span class="icon-[tabler--indent-increase] size-4"></span>
                            <span>Indent</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <div class="toolbar-dropdown" data-dropdown="insert">
                    <button type="button" class="toolbar-dropdown-btn">
                        <span class="icon-[tabler--plus] size-4"></span>
                        <span class="dropdown-label">Insert</span>
                        <span class="icon-[tabler--chevron-down] size-3.5"></span>
                    </button>
                    <div class="toolbar-dropdown-menu insert-menu">
                        <button type="button" class="dropdown-item" data-insert="hr">
                            <span class="icon-[tabler--separator-horizontal] size-4"></span>
                            <span>Horizontal Rule</span>
                        </button>
                        <button type="button" class="dropdown-item" data-insert="page-break">
                            <span class="icon-[tabler--page-break] size-4"></span>
                            <span>Page Break</span>
                        </button>
                        <button type="button" class="dropdown-item" data-insert="image">
                            <span class="icon-[tabler--photo] size-4"></span>
                            <span>Image</span>
                        </button>
                        <button type="button" class="dropdown-item" data-insert="inline-image">
                            <span class="icon-[tabler--photo-plus] size-4"></span>
                            <span>Inline Image</span>
                        </button>
                        <button type="button" class="dropdown-item" data-insert="gif">
                            <span class="icon-[tabler--gif] size-4"></span>
                            <span>GIF</span>
                        </button>
                        <button type="button" class="dropdown-item" data-insert="excalidraw">
                            <span class="icon-[tabler--pencil] size-4"></span>
                            <span>Excalidraw</span>
                        </button>
                        <button type="button" class="dropdown-item" data-insert="table">
                            <span class="icon-[tabler--table] size-4"></span>
                            <span>Table</span>
                        </button>
                        <button type="button" class="dropdown-item" data-insert="poll">
                            <span class="icon-[tabler--chart-bar] size-4"></span>
                            <span>Poll</span>
                        </button>
                        <div class="dropdown-divider"></div>
                        <button type="button" class="dropdown-item" data-insert="columns">
                            <span class="icon-[tabler--columns] size-4"></span>
                            <span>Columns Layout</span>
                        </button>
                        <button type="button" class="dropdown-item" data-insert="equation">
                            <span class="icon-[tabler--math] size-4"></span>
                            <span>Equation</span>
                        </button>
                        <button type="button" class="dropdown-item" data-insert="sticky-note">
                            <span class="icon-[tabler--note] size-4"></span>
                            <span>Sticky Note</span>
                        </button>
                        <button type="button" class="dropdown-item" data-insert="collapsible">
                            <span class="icon-[tabler--caret-right] size-4"></span>
                            <span>Collapsible</span>
                        </button>
                        <div class="dropdown-divider"></div>
                        <button type="button" class="dropdown-item" data-insert="tweet">
                            <span class="icon-[tabler--brand-x] size-4"></span>
                            <span>Tweet</span>
                        </button>
                        <button type="button" class="dropdown-item" data-insert="youtube">
                            <span class="icon-[tabler--brand-youtube] size-4"></span>
                            <span>YouTube Video</span>
                        </button>
                        <button type="button" class="dropdown-item" data-insert="figma">
                            <span class="icon-[tabler--brand-figma] size-4"></span>
                            <span>Figma</span>
                        </button>
                    </div>
                </div>
            </div>
        `;

        this.container.insertBefore(this.toolbarElement, this.container.firstChild);
        this.bindToolbarEvents();
    }

    bindToolbarEvents() {
        // Format buttons (bold, italic, etc.)
        this.toolbarElement.querySelectorAll('[data-format]').forEach(btn => {
            btn.addEventListener('click', () => {
                const format = btn.dataset.format;
                this.editor.dispatchCommand(FORMAT_TEXT_COMMAND, format);
            });
        });

        // Undo/Redo
        this.toolbarElement.querySelector('[data-action="undo"]')?.addEventListener('click', () => {
            this.editor.dispatchCommand(UNDO_COMMAND, undefined);
        });
        this.toolbarElement.querySelector('[data-action="redo"]')?.addEventListener('click', () => {
            this.editor.dispatchCommand(REDO_COMMAND, undefined);
        });

        // Clear formatting
        this.toolbarElement.querySelector('[data-action="clear-format"]')?.addEventListener('click', () => {
            this.editor.update(() => {
                const selection = $getSelection();
                if ($isRangeSelection(selection)) {
                    const anchor = selection.anchor;
                    const focus = selection.focus;
                    const nodes = selection.getNodes();

                    if (anchor.key === focus.key && anchor.offset === focus.offset) {
                        return;
                    }

                    nodes.forEach(node => {
                        if (node.setFormat) {
                            node.setFormat(0);
                        }
                        if (node.setStyle) {
                            node.setStyle('');
                        }
                    });
                }
            });
        });

        // Link button
        this.toolbarElement.querySelector('[data-action="link"]')?.addEventListener('click', () => {
            this.insertLink();
        });

        // Block type dropdown
        const blockDropdown = this.toolbarElement.querySelector('[data-dropdown="block-type"]');
        if (blockDropdown) {
            const btn = blockDropdown.querySelector('.toolbar-dropdown-btn');
            const menu = blockDropdown.querySelector('.toolbar-dropdown-menu');

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeAllDropdowns();
                blockDropdown.classList.toggle('open');
            });

            menu.querySelectorAll('[data-block]').forEach(item => {
                item.addEventListener('click', () => {
                    const blockType = item.dataset.block;
                    this.formatBlock(blockType);
                    blockDropdown.classList.remove('open');
                });
            });
        }

        // Font family dropdown
        const fontDropdown = this.toolbarElement.querySelector('[data-dropdown="font-family"]');
        if (fontDropdown) {
            const btn = fontDropdown.querySelector('.toolbar-dropdown-btn');
            const menu = fontDropdown.querySelector('.toolbar-dropdown-menu');

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeAllDropdowns();
                fontDropdown.classList.toggle('open');
            });

            menu.querySelectorAll('[data-font]').forEach(item => {
                item.addEventListener('click', () => {
                    const font = item.dataset.font;
                    this.applyFontFamily(font);
                    fontDropdown.classList.remove('open');
                    // Update label
                    const label = fontDropdown.querySelector('.font-label');
                    if (label) label.textContent = font;
                    // Update active state
                    menu.querySelectorAll('[data-font]').forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                });
            });
        }

        // Font size dropdown
        const sizeDropdown = this.toolbarElement.querySelector('[data-dropdown="font-size"]');
        if (sizeDropdown) {
            const btn = sizeDropdown.querySelector('.toolbar-dropdown-btn');
            const menu = sizeDropdown.querySelector('.toolbar-dropdown-menu');

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeAllDropdowns();
                sizeDropdown.classList.toggle('open');
            });

            menu.querySelectorAll('[data-size]').forEach(item => {
                item.addEventListener('click', () => {
                    const size = item.dataset.size;
                    this.applyFontSize(size + 'px');
                    sizeDropdown.classList.remove('open');
                    // Update label
                    const label = sizeDropdown.querySelector('.size-label');
                    if (label) label.textContent = size + 'px';
                    // Update active state
                    menu.querySelectorAll('[data-size]').forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                });
            });
        }

        // Text color dropdown
        const textColorDropdown = this.toolbarElement.querySelector('[data-dropdown="text-color"]');
        if (textColorDropdown) {
            const btn = textColorDropdown.querySelector('.toolbar-dropdown-btn');
            const menu = textColorDropdown.querySelector('.toolbar-dropdown-menu');

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeAllDropdowns();
                textColorDropdown.classList.toggle('open');
            });

            menu.querySelectorAll('.color-item').forEach(item => {
                item.addEventListener('click', () => {
                    const color = item.dataset.color;
                    this.applyTextColor(color);
                    textColorDropdown.classList.remove('open');
                    // Update indicator
                    const indicator = textColorDropdown.querySelector('.color-indicator');
                    if (indicator) indicator.style.background = color;
                });
            });
        }

        // Background color dropdown
        const bgColorDropdown = this.toolbarElement.querySelector('[data-dropdown="bg-color"]');
        if (bgColorDropdown) {
            const btn = bgColorDropdown.querySelector('.toolbar-dropdown-btn');
            const menu = bgColorDropdown.querySelector('.toolbar-dropdown-menu');

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeAllDropdowns();
                bgColorDropdown.classList.toggle('open');
            });

            menu.querySelectorAll('.color-item').forEach(item => {
                item.addEventListener('click', () => {
                    const color = item.dataset.color;
                    this.applyBackgroundColor(color);
                    bgColorDropdown.classList.remove('open');
                    // Update indicator
                    const indicator = bgColorDropdown.querySelector('.bg-indicator');
                    if (indicator) {
                        if (color === 'transparent') {
                            indicator.style.background = 'transparent';
                            indicator.style.border = '1px dashed #ccc';
                        } else {
                            indicator.style.background = color;
                            indicator.style.border = 'none';
                        }
                    }
                });
            });
        }

        // List buttons (standalone, if any)
        this.toolbarElement.querySelectorAll('[data-list]').forEach(btn => {
            btn.addEventListener('click', () => {
                const listType = btn.dataset.list;
                if (listType === 'bullet') {
                    if (this.currentBlockType === 'bullet') {
                        this.editor.dispatchCommand(REMOVE_LIST_COMMAND, undefined);
                    } else {
                        this.editor.dispatchCommand(INSERT_UNORDERED_LIST_COMMAND, undefined);
                    }
                } else if (listType === 'number') {
                    if (this.currentBlockType === 'number') {
                        this.editor.dispatchCommand(REMOVE_LIST_COMMAND, undefined);
                    } else {
                        this.editor.dispatchCommand(INSERT_ORDERED_LIST_COMMAND, undefined);
                    }
                }
            });
        });

        // Alignment dropdown
        const alignDropdown = this.toolbarElement.querySelector('[data-dropdown="align"]');
        if (alignDropdown) {
            const btn = alignDropdown.querySelector('.toolbar-dropdown-btn');
            const menu = alignDropdown.querySelector('.toolbar-dropdown-menu');

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeAllDropdowns();
                alignDropdown.classList.toggle('open');
            });

            menu.querySelectorAll('[data-align]').forEach(item => {
                item.addEventListener('click', () => {
                    const align = item.dataset.align;
                    this.editor.dispatchCommand(FORMAT_ELEMENT_COMMAND, align);
                    this.updateAlignIcon(align);
                    alignDropdown.classList.remove('open');
                });
            });

            // Indent/Outdent
            menu.querySelectorAll('[data-indent]').forEach(item => {
                item.addEventListener('click', () => {
                    const indent = item.dataset.indent;
                    if (indent === 'indent') {
                        this.editor.dispatchCommand(INDENT_CONTENT_COMMAND, undefined);
                    } else if (indent === 'outdent') {
                        this.editor.dispatchCommand(OUTDENT_CONTENT_COMMAND, undefined);
                    }
                    alignDropdown.classList.remove('open');
                });
            });
        }

        // Insert dropdown
        const insertDropdown = this.toolbarElement.querySelector('[data-dropdown="insert"]');
        if (insertDropdown) {
            const btn = insertDropdown.querySelector('.toolbar-dropdown-btn');
            const menu = insertDropdown.querySelector('.toolbar-dropdown-menu');

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeAllDropdowns();
                insertDropdown.classList.toggle('open');
            });

            menu.querySelectorAll('[data-insert]').forEach(item => {
                item.addEventListener('click', () => {
                    const insertType = item.dataset.insert;
                    this.handleInsert(insertType);
                    insertDropdown.classList.remove('open');
                });
            });
        }

        // Close dropdowns on outside click
        document.addEventListener('click', () => {
            this.closeAllDropdowns();
        });
    }

    applyFontFamily(fontFamily) {
        this.editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                selection.getNodes().forEach(node => {
                    if (node.setStyle) {
                        const currentStyle = node.getStyle() || '';
                        const newStyle = this.updateStyleProperty(currentStyle, 'font-family', fontFamily);
                        node.setStyle(newStyle);
                    }
                });
            }
        });
    }

    applyFontSize(fontSize) {
        this.editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                selection.getNodes().forEach(node => {
                    if (node.setStyle) {
                        const currentStyle = node.getStyle() || '';
                        const newStyle = this.updateStyleProperty(currentStyle, 'font-size', fontSize);
                        node.setStyle(newStyle);
                    }
                });
            }
        });
    }

    applyTextColor(color) {
        this.editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                selection.getNodes().forEach(node => {
                    if (node.setStyle) {
                        const currentStyle = node.getStyle() || '';
                        const newStyle = this.updateStyleProperty(currentStyle, 'color', color);
                        node.setStyle(newStyle);
                    }
                });
            }
        });
    }

    applyBackgroundColor(color) {
        this.editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                selection.getNodes().forEach(node => {
                    if (node.setStyle) {
                        const currentStyle = node.getStyle() || '';
                        let newStyle;
                        if (color === 'transparent') {
                            newStyle = this.removeStyleProperty(currentStyle, 'background-color');
                        } else {
                            newStyle = this.updateStyleProperty(currentStyle, 'background-color', color);
                        }
                        node.setStyle(newStyle);
                    }
                });
            }
        });
    }

    updateStyleProperty(styleString, property, value) {
        const styles = styleString ? styleString.split(';').filter(s => s.trim()) : [];
        const styleMap = {};
        styles.forEach(s => {
            const [key, val] = s.split(':').map(x => x.trim());
            if (key && val) styleMap[key] = val;
        });
        styleMap[property] = value;
        return Object.entries(styleMap).map(([k, v]) => `${k}: ${v}`).join('; ');
    }

    removeStyleProperty(styleString, property) {
        const styles = styleString ? styleString.split(';').filter(s => s.trim()) : [];
        const styleMap = {};
        styles.forEach(s => {
            const [key, val] = s.split(':').map(x => x.trim());
            if (key && val && key !== property) styleMap[key] = val;
        });
        return Object.entries(styleMap).map(([k, v]) => `${k}: ${v}`).join('; ');
    }

    closeAllDropdowns() {
        this.toolbarElement.querySelectorAll('.toolbar-dropdown.open').forEach(d => d.classList.remove('open'));
    }

    updateAlignIcon(align) {
        const alignIcon = this.toolbarElement.querySelector('.align-icon');
        if (alignIcon) {
            alignIcon.className = `align-icon icon-[tabler--align-${align}] size-4`;
        }
        // Update active state in menu
        const alignDropdown = this.toolbarElement.querySelector('[data-dropdown="align"]');
        if (alignDropdown) {
            alignDropdown.querySelectorAll('[data-align]').forEach(item => {
                item.classList.toggle('active', item.dataset.align === align);
            });
        }
    }

    handleInsert(insertType) {
        switch (insertType) {
            case 'link':
                this.insertLink();
                break;
            case 'image':
            case 'inline-image':
                this.insertImage();
                break;
            case 'hr':
                this.insertHorizontalRule();
                break;
            case 'page-break':
                this.insertPageBreak();
                break;
            case 'table':
                this.insertTable();
                break;
            case 'gif':
                this.insertGif();
                break;
            case 'excalidraw':
                this.showNotImplemented('Excalidraw drawing');
                break;
            case 'poll':
                this.showNotImplemented('Poll');
                break;
            case 'columns':
                this.insertColumns();
                break;
            case 'equation':
                this.insertEquation();
                break;
            case 'sticky-note':
                this.insertStickyNote();
                break;
            case 'collapsible':
                this.insertCollapsible();
                break;
            case 'tweet':
                this.insertEmbed('tweet');
                break;
            case 'youtube':
                this.insertEmbed('youtube');
                break;
            case 'figma':
                this.insertEmbed('figma');
                break;
        }
    }

    showNotImplemented(feature) {
        alert(`${feature} feature coming soon!`);
    }

    insertPageBreak() {
        this.editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                const paragraph = $createParagraphNode();
                paragraph.append($createTextNode('─────────────── Page Break ───────────────'));
                selection.insertNodes([paragraph]);
            }
        });
    }

    insertGif() {
        const url = prompt('Enter GIF URL (from Giphy, Tenor, etc.):');
        if (url && url.trim()) {
            this.editor.update(() => {
                const selection = $getSelection();
                if ($isRangeSelection(selection)) {
                    const paragraph = $createParagraphNode();
                    paragraph.append($createTextNode(`[GIF: ${url.trim()}]`));
                    selection.insertNodes([paragraph]);
                }
            });
        }
    }

    insertColumns() {
        this.editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                const paragraph = $createParagraphNode();
                paragraph.append($createTextNode('[2-Column Layout]'));
                selection.insertNodes([paragraph]);
            }
        });
    }

    insertEquation() {
        const equation = prompt('Enter LaTeX equation (e.g., E = mc^2):');
        if (equation && equation.trim()) {
            this.editor.update(() => {
                const selection = $getSelection();
                if ($isRangeSelection(selection)) {
                    const paragraph = $createParagraphNode();
                    paragraph.append($createTextNode(`$${equation.trim()}$`));
                    selection.insertNodes([paragraph]);
                }
            });
        }
    }

    insertStickyNote() {
        this.editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                const paragraph = $createParagraphNode();
                paragraph.append($createTextNode('[📝 Sticky Note: Click to edit]'));
                selection.insertNodes([paragraph]);
            }
        });
    }

    insertCollapsible() {
        this.editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                const paragraph = $createParagraphNode();
                paragraph.append($createTextNode('▶ Collapsible Section'));
                selection.insertNodes([paragraph]);
            }
        });
    }

    insertEmbed(type) {
        let placeholder = '';
        let prompt_text = '';

        switch (type) {
            case 'tweet':
                prompt_text = 'Enter Tweet URL:';
                placeholder = '[Tweet Embed]';
                break;
            case 'youtube':
                prompt_text = 'Enter YouTube Video URL:';
                placeholder = '[YouTube Video]';
                break;
            case 'figma':
                prompt_text = 'Enter Figma File URL:';
                placeholder = '[Figma Embed]';
                break;
        }

        const url = prompt(prompt_text);
        if (url && url.trim()) {
            this.editor.update(() => {
                const selection = $getSelection();
                if ($isRangeSelection(selection)) {
                    const paragraph = $createParagraphNode();
                    paragraph.append($createTextNode(`${placeholder}: ${url.trim()}`));
                    selection.insertNodes([paragraph]);
                }
            });
        }
    }

    insertImage() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.onchange = () => {
            const file = input.files[0];
            if (file) {
                // Dispatch custom event for image upload
                const event = new CustomEvent('lexical-image-upload', { detail: { file } });
                document.dispatchEvent(event);
            }
        };
        input.click();
    }

    insertHorizontalRule() {
        this.editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                const paragraph = $createParagraphNode();
                paragraph.append($createTextNode('───────────────────────────────'));
                selection.insertNodes([paragraph]);
            }
        });
    }

    insertTable() {
        // Simple 3x3 table insertion
        alert('Table insertion coming soon. Use markdown syntax: | Col1 | Col2 |');
    }

    formatBlock(blockType) {
        // Handle list types via commands
        if (blockType === 'bullet') {
            if (this.currentBlockType === 'bullet') {
                this.editor.dispatchCommand(REMOVE_LIST_COMMAND, undefined);
            } else {
                this.editor.dispatchCommand(INSERT_UNORDERED_LIST_COMMAND, undefined);
            }
            return;
        }
        if (blockType === 'number') {
            if (this.currentBlockType === 'number') {
                this.editor.dispatchCommand(REMOVE_LIST_COMMAND, undefined);
            } else {
                this.editor.dispatchCommand(INSERT_ORDERED_LIST_COMMAND, undefined);
            }
            return;
        }
        if (blockType === 'check') {
            if (this.currentBlockType === 'check') {
                this.editor.dispatchCommand(REMOVE_LIST_COMMAND, undefined);
            } else {
                this.editor.dispatchCommand(INSERT_CHECK_LIST_COMMAND, undefined);
            }
            return;
        }

        // Handle block types via setBlocksType
        this.editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                if (blockType === 'paragraph') {
                    $setBlocksType(selection, () => $createParagraphNode());
                } else if (blockType === 'h1') {
                    $setBlocksType(selection, () => $createHeadingNode('h1'));
                } else if (blockType === 'h2') {
                    $setBlocksType(selection, () => $createHeadingNode('h2'));
                } else if (blockType === 'h3') {
                    $setBlocksType(selection, () => $createHeadingNode('h3'));
                } else if (blockType === 'h4') {
                    $setBlocksType(selection, () => $createHeadingNode('h4'));
                } else if (blockType === 'h5') {
                    $setBlocksType(selection, () => $createHeadingNode('h5'));
                } else if (blockType === 'h6') {
                    $setBlocksType(selection, () => $createHeadingNode('h6'));
                } else if (blockType === 'quote') {
                    $setBlocksType(selection, () => $createQuoteNode());
                } else if (blockType === 'code') {
                    $setBlocksType(selection, () => $createCodeNode());
                }
            }
        });
    }

    insertLink() {
        this.editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                const nodes = selection.getNodes();
                const isLink = nodes.some(node => {
                    const parent = node.getParent();
                    return $isLinkNode(parent) || $isLinkNode(node);
                });

                if (isLink) {
                    this.editor.dispatchCommand(TOGGLE_LINK_COMMAND, null);
                } else {
                    const url = prompt('Enter URL:');
                    if (url && url.trim()) {
                        let finalUrl = url.trim();
                        if (!finalUrl.startsWith('http://') && !finalUrl.startsWith('https://')) {
                            finalUrl = 'https://' + finalUrl;
                        }
                        this.editor.dispatchCommand(TOGGLE_LINK_COMMAND, { url: finalUrl });
                    }
                }
            }
        });
    }

    registerListeners() {
        return mergeRegister(
            this.editor.registerUpdateListener(({ editorState }) => {
                editorState.read(() => {
                    this.updateToolbar();
                });
            }),
            this.editor.registerCommand(
                UNDO_COMMAND,
                () => {
                    return false;
                },
                COMMAND_PRIORITY_CRITICAL
            )
        );
    }

    updateToolbar() {
        const selection = $getSelection();
        if ($isRangeSelection(selection)) {
            // Update text format buttons
            const formats = ['bold', 'italic', 'underline', 'strikethrough', 'code'];
            formats.forEach(format => {
                const isActive = selection.hasFormat(format);
                const btn = this.toolbarElement.querySelector(`[data-format="${format}"]`);
                if (btn) {
                    btn.classList.toggle('active', isActive);
                }
            });

            // Update block type
            const anchorNode = selection.anchor.getNode();
            let element = anchorNode.getKey() === 'root'
                ? anchorNode
                : $findMatchingParent(anchorNode, (e) => {
                    const parent = e.getParent();
                    return parent !== null && $isRootOrShadowRoot(parent);
                });

            if (element === null) {
                element = anchorNode.getTopLevelElementOrThrow();
            }

            const elementKey = element.getKey();
            const elementDOM = this.editor.getElementByKey(elementKey);

            if (elementDOM !== null) {
                // Lists
                if ($isListNode(element)) {
                    const parentList = $getNearestNodeOfType(anchorNode, ListNode);
                    const type = parentList ? parentList.getListType() : element.getListType();
                    this.currentBlockType = type === 'bullet' ? 'bullet' : 'number';
                } else {
                    const type = $isHeadingNode(element) ? element.getTag() : element.getType();
                    if (type in blockTypeToBlockName) {
                        this.currentBlockType = type;
                    } else {
                        this.currentBlockType = 'paragraph';
                    }
                }
            }

            // Update block type dropdown label
            this.updateBlockTypeLabel();

            // Check for links
            const linkBtn = this.toolbarElement.querySelector('[data-action="link"]');
            if (linkBtn) {
                const nodes = selection.getNodes();
                const isLink = nodes.some(node => {
                    const parent = node.getParent();
                    return $isLinkNode(parent) || $isLinkNode(node);
                });
                linkBtn.classList.toggle('active', isLink);
            }

            // Update list buttons
            const bulletBtn = this.toolbarElement.querySelector('[data-list="bullet"]');
            const numberBtn = this.toolbarElement.querySelector('[data-list="number"]');
            if (bulletBtn) bulletBtn.classList.toggle('active', this.currentBlockType === 'bullet');
            if (numberBtn) numberBtn.classList.toggle('active', this.currentBlockType === 'number');
        }
    }

    updateBlockTypeLabel() {
        const dropdown = this.toolbarElement.querySelector('[data-dropdown="block-type"]');
        if (!dropdown) return;

        const label = dropdown.querySelector('.dropdown-label');
        const name = blockTypeToBlockName[this.currentBlockType] || 'Normal';
        label.textContent = name;

        // Update active state in menu
        dropdown.querySelectorAll('[data-block]').forEach(item => {
            const isActive = item.dataset.block === this.currentBlockType;
            item.classList.toggle('active', isActive);
        });
    }

    setCanUndo(canUndo) {
        this.canUndo = canUndo;
        const btn = this.toolbarElement.querySelector('[data-action="undo"]');
        if (btn) btn.disabled = !canUndo;
    }

    setCanRedo(canRedo) {
        this.canRedo = canRedo;
        const btn = this.toolbarElement.querySelector('[data-action="redo"]');
        if (btn) btn.disabled = !canRedo;
    }

    setReadOnly(readOnly) {
        this.toolbarElement.querySelectorAll('button').forEach(btn => {
            btn.disabled = readOnly;
        });
        this.toolbarElement.classList.toggle('disabled', readOnly);
    }
}

const blockTypeToBlockName = {
    paragraph: 'Normal',
    h1: 'Heading 1',
    h2: 'Heading 2',
    h3: 'Heading 3',
    h4: 'Heading 4',
    h5: 'Heading 5',
    h6: 'Heading 6',
    quote: 'Quote',
    code: 'Code Block',
    bullet: 'Bullet List',
    number: 'Numbered List',
    check: 'Check List',
};

// Need to import ListNode for the type check
import { ListNode } from '@lexical/list';

export default LexicalToolbar;
