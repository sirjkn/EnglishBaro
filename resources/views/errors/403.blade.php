@include('errors.minimal', [
    'code' => 403,
    'title' => 'Access Denied',
    'message' => $exception->getMessage() ?: "You don't have permission to access this page.",
])
