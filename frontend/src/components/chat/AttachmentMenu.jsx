/**
 * AttachmentMenu Component
 * Menú desplegable para adjuntar archivos
 */

import React from 'react';
import './AttachmentMenu.scss';

const AttachmentMenu = ({ onSelect, onClose }) => {
  return (
    <div className="attachment-menu">
      <ul className="attachment-options">
        <li onClick={() => onSelect('image')}>
          <div className="option-icon image">
            <svg viewBox="0 0 24 24" width="24" height="24">
              <path fill="currentColor" d="M21.5 2h-19c-1.379 0-2.5 1.122-2.5 2.5v15c0 1.378 1.121 2.5 2.5 2.5h19c1.379 0 2.5-1.122 2.5-2.5v-15c0-1.378-1.121-2.5-2.5-2.5zm.9 17.5c0 .497-.403.9-.9.9h-19c-.497 0-.9-.403-.9-.9v-15c0-.497.403-.9.9-.9h19c.497 0 .9.403.9.9v15zM7 14.381l3.58 4.476 4.67-6.225 5.25 7.868H3.5l3.5-6.119z"></path>
            </svg>
          </div>
          <span className="option-label">Fotos y videos</span>
        </li>
        <li onClick={() => onSelect('document')}>
          <div className="option-icon document">
            <svg viewBox="0 0 24 24" width="24" height="24">
              <path fill="currentColor" d="M13.5 2H6c-1.103 0-2 .897-2 2v16c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2V8.5L13.5 2zM6 20V4h7v5h5v11H6z"></path>
            </svg>
          </div>
          <span className="option-label">Documento</span>
        </li>
      </ul>
    </div>
  );
};

export default AttachmentMenu;
