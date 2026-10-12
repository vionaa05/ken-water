class UserModel {
  final int id;
  final String name;
  final String? email;
  final String? phone;
  final String? address;
  final String role; // 'admin' atau 'pelanggan'
  final String customerId;
  final String loyaltyLevel;
  final int points;
  final String? status;
  final LoyaltyProgress? progress;

  UserModel({
    required this.id,
    required this.name,
    this.email,
    this.phone,
    this.address,
    required this.role,
    required this.customerId,
    required this.loyaltyLevel,
    required this.points,
    this.status,
    this.progress,
  });

  bool get isAdmin => role == 'admin';
  bool get isCustomer => role == 'pelanggan';

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      name: json['name'] ?? '',
      email: json['email'],
      phone: json['phone'],
      address: json['address'],
      role: json['role'] ?? 'pelanggan',
      customerId: json['customer_id'] ?? '',
      loyaltyLevel: (json['loyalty_level'] ?? 'bronze').toString().toLowerCase(),
      points: json['points'] is int ? json['points'] : int.tryParse(json['points'].toString()) ?? 0,
      status: json['status'],
      progress: json['progress'] != null && json['progress'] is Map<String, dynamic>
          ? LoyaltyProgress.fromJson(json['progress'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'address': address,
      'role': role,
      'customer_id': customerId,
      'loyalty_level': loyaltyLevel,
      'points': points,
      'status': status,
    };
  }
}

class LoyaltyProgress {
  final int current;
  final int target;
  final int pointsNeeded;
  final String? nextLevel;
  final bool isMax;
  final num percentage;

  LoyaltyProgress({
    required this.current,
    required this.target,
    required this.pointsNeeded,
    this.nextLevel,
    required this.isMax,
    required this.percentage,
  });

  factory LoyaltyProgress.fromJson(Map<String, dynamic> json) {
    return LoyaltyProgress(
      current: json['current'] ?? 0,
      target: json['target'] ?? 0,
      pointsNeeded: json['points_needed'] ?? json['needed'] ?? 0,
      nextLevel: json['next_level'],
      isMax: json['is_max'] ?? false,
      percentage: json['percentage'] ?? 0,
    );
  }
}
