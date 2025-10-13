import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";

interface FormAlertProps {
  title?: string;
  message: string;
  variant?: "default" | "destructive";
  onDismiss: () => void;
}

export const FormAlert: React.FC<FormAlertProps> = ({
  title = "Error",
  message,
  variant = "destructive",
  onDismiss,
}) => (
  <Alert
    variant={variant}
    className="mb-4 flex items-start justify-between"
  >
    <div>
      <AlertTitle>{title}</AlertTitle>
      <AlertDescription>{message}</AlertDescription>
    </div>
    <button
      onClick={onDismiss}
      className="ml-4 text-sm font-medium text-red-700 hover:underline"
    >
      Dismiss
    </button>
  </Alert>
);
