import 'package:flutter/material.dart';

/// Shimmer-like loading placeholder for content areas.
class LoadingShimmer extends StatefulWidget {
  const LoadingShimmer({
    super.key,
    this.itemCount = 5,
    this.message,
  });

  final int itemCount;
  final String? message;

  @override
  State<LoadingShimmer> createState() => _LoadingShimmerState();
}

class _LoadingShimmerState extends State<LoadingShimmer>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..repeat(reverse: true);
    _animation = Tween<double>(begin: 0.3, end: 0.7).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final baseColor = isDark ? Colors.white : Colors.grey;

    return Column(
      children: [
        if (widget.message != null) ...[
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: theme.colorScheme.primary,
                ),
              ),
              const SizedBox(width: 10),
              Text(
                widget.message!,
                style: theme.textTheme.bodySmall?.copyWith(
                  color: theme.colorScheme.onSurface.withValues(alpha: 0.5),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
        ],
        Expanded(
          child: AnimatedBuilder(
            animation: _animation,
            builder: (context, child) {
              return ListView.builder(
                physics: const NeverScrollableScrollPhysics(),
                itemCount: widget.itemCount,
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                itemBuilder: (_, i) => _ShimmerCard(
                  opacity: _animation.value,
                  baseColor: baseColor,
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}

class _ShimmerCard extends StatelessWidget {
  const _ShimmerCard({required this.opacity, required this.baseColor});
  final double opacity;
  final Color baseColor;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _shimmerBlock(width: 180, height: 14),
            const SizedBox(height: 10),
            _shimmerBlock(width: 260, height: 10),
            const SizedBox(height: 6),
            Row(
              children: [
                _shimmerBlock(width: 70, height: 10),
                const SizedBox(width: 12),
                _shimmerBlock(width: 90, height: 10),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _shimmerBlock({required double width, required double height}) {
    return Container(
      width: width,
      height: height,
      decoration: BoxDecoration(
        color: baseColor.withValues(alpha: opacity * 0.15),
        borderRadius: BorderRadius.circular(6),
      ),
    );
  }
}
