abstract class IzinState {
  const IzinState();
}

class IzinInitial extends IzinState {
  const IzinInitial();
}

class IzinLoading extends IzinState {
  const IzinLoading();
}

class IzinLoaded extends IzinState {
  final List<Map<String, dynamic>> izinList;

  const IzinLoaded({required this.izinList});
}

class IzinSubmitting extends IzinState {
  const IzinSubmitting();
}

class IzinSuccess extends IzinState {
  final String message;

  const IzinSuccess(this.message);
}

class IzinError extends IzinState {
  final String message;

  const IzinError(this.message);
}
