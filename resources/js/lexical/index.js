/**
 * Lexical Editor Entry Point
 * Import this file to use the Lexical editor
 */

import './styles.css';
import { LexicalEditor } from './editor.js';
import { LexicalToolbar } from './toolbar.js';
import { editorTheme } from './theme.js';

// Export for ES module usage
export { LexicalEditor, LexicalToolbar, editorTheme };

// Make available globally
if (typeof window !== 'undefined') {
    window.LexicalEditor = LexicalEditor;
    window.LexicalToolbar = LexicalToolbar;
    window.lexicalTheme = editorTheme;
}

export default LexicalEditor;
