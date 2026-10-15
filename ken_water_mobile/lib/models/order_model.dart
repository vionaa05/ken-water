class OrderModel {
  final int id;
  final String orderDate;
  final int gallonQty;
  final double unitPrice;
  final double totalPrice;
  final String? deliveryMethod;
  final String? deliveryAddress;
  final String? paymentMethod;
  final String status;
  final String statusLabel;
  final String? customerName;
  final String? customerId;
  final String? customerPhone;
  final String? customerLevel;
  final String? source;
  final String? notes;
  final String? _orderNumber;

  OrderModel({
    required this.id,
    required this.orderDate,
    required this.gallonQty,
    required this.unitPrice,
    required this.totalPrice,
    this.deliveryMethod,
    this.deliveryAddress,
    this.paymentMethod,
    required this.status,
    required this.statusLabel,
    this.customerName,
    this.customerId,
    this.customerPhone,
    this.customerLevel,
    this.source,
    this.notes,
    String? orderNumber,
  }) : _orderNumber = orderNumber;

  String get orderNumber =>
      _orderNumber ?? 'ORD-${id.toString().padLeft(5, '0')}';

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    return OrderModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      orderDate: json['order_date'] ?? json['date'] ?? json['created_at']?.toString().substring(0, 16) ?? '',
      gallonQty: json['gallon_qty'] is int ? json['gallon_qty'] : int.tryParse(json['gallon_qty'].toString()) ?? 0,
      unitPrice: (json['unit_price'] != null) ? (json['unit_price'] as num).toDouble() : 0.0,
      totalPrice: (json['total_price'] != null) ? (json['total_price'] as num).toDouble() : 0.0,
      deliveryMethod: json['delivery_method'],
      deliveryAddress: json['delivery_address'] ?? json['address'],
      paymentMethod: json['payment_method'],
      status: json['status'] ?? 'selesai',
      statusLabel: json['status_label'] ?? json['status'] ?? 'Selesai',
      customerName: json['customer_name'] ?? json['customer']?['name'],
      customerId: json['customer_id'] ?? json['customer']?['customer_id'],
      customerPhone: json['customer_phone'] ?? json['user']?['phone'] ?? json['customer']?['phone'],
      customerLevel: json['customer_level'] ?? json['user']?['loyalty_level'] ?? json['customer']?['loyalty_level'] ?? 'bronze',
      source: json['source'],
      notes: json['notes'],
      orderNumber: json['order_number'] ?? (json['id'] != null ? 'ORD-${json['id'].toString().padLeft(5, '0')}' : null),
    );
  }
}
