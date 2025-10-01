import UserCreate from '../UserCreate';

export default function Index() {
  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-4">Users Management</h1>
      <UserCreate />
    </div>
  );
}
