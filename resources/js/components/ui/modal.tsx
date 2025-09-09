import { ReactNode } from "react";

interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  children: ReactNode;
  widthClass?: string; // optional width (e.g., "max-w-4xl")
}

export default function Modal({
  isOpen,
  onClose,
  title,
  children,
  widthClass = "max-w-2xl",
}: ModalProps) {
  if (!isOpen) return null;

  return (
    <div
      className="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 dark:bg-opacity-70 backdrop-blur-sm z-50 p-4"
      onClick={onClose} // close when clicking outside
    >
      <div
        className={`w-full ${widthClass} max-h-[85vh] overflow-y-auto p-6 relative rounded-lg shadow-lg
          bg-white text-gray-900 
          dark:bg-gray-800 dark:text-gray-100`}
        onClick={(e) => e.stopPropagation()} // prevent close inside
      >
        {/* Close Button */}
        <button
          className="absolute top-3 right-3 text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
          onClick={onClose}
        >
          ✕
        </button>

        {title && (
          <h3 className="text-xl font-bold mb-4 text-center">{title}</h3>
        )}

        {children}
      </div>
    </div>
  );
}
