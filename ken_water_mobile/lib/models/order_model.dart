class OrderModel {
  final int id;
  final String orderDate;
  final int gallonQty;
  final double unitPrice;
  final double totalPrice;
  final String? deliveryMethod;
  final String? paymentMethod;
  final String status;
  final String statusLabel;
  final String? customerName;
  final String? customerId;
  final String? notes;

  OrderModel({
    required this.id,
    required this.orderDate,
    required this.gallonQty,
    required this.unitPrice,
    required this.totalPrice,
    this.deliveryMethod,
    this.paymentMethod,
    required this.status,
    required this.statusLabel,
    this.customerName,
    this.customerId,
    this.notes,
  });

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    return OrderModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      orderDate: json['order_date'] ?? json['date'] ?? json['time'] ?? '',
      gallonQty: json['gallon_qty'] is int ? json['gallon_qty'] : int.tryParse(json['gallon_qty'].toString()) ?? 0,
      unitPrice: (json['unit_price'] != null) ? (json['unit_price'] as num).toDouble() : 0.0,
      totalPrice: (json['total_price'] != null) ? (json['total_price'] as num).toDouble() : 0.0,
      deliveryMethod: json['delivery_method'],
      paymentMethod: json['payment_method'],
      status: json['status'] ?? 'selesai',
      statusLabel: json['status_label'] ?? json['status'] ?? 'Selesai',
      customerName: json['customer_name'],
      customerId: json['customer_id'],
      notes: json['notes'],
    );
  }
}
