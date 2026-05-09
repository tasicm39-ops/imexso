type DayStat = { date: string; count: number };

type SparkBarProps = {
    data: DayStat[];
    color?: string;
    className?: string;
};

const BAR_WIDTH = 6;
const BAR_GAP = 2;
const MAX_HEIGHT = 20;
const SVG_WIDTH = 5 * BAR_WIDTH + 4 * BAR_GAP;

export default function SparkBar({ data, color = '#3b82f6', className }: SparkBarProps) {
    const max = Math.max(...data.map((d) => d.count), 1);
    const total = data.reduce((sum, d) => sum + d.count, 0);

    return (
        <div className={`inline-flex items-center gap-1.5 ${className ?? ''}`}>
            <svg width={SVG_WIDTH} height={MAX_HEIGHT} className="shrink-0">
                {data.map((d, i) => {
                    const h = (d.count / max) * MAX_HEIGHT || 1;
                    return (
                        <rect
                            key={d.date}
                            x={i * (BAR_WIDTH + BAR_GAP)}
                            y={MAX_HEIGHT - h}
                            width={BAR_WIDTH}
                            height={h}
                            rx={1}
                            fill={d.count > 0 ? color : '#e5e7eb'}
                        />
                    );
                })}
            </svg>
            <span className="text-xs tabular-nums text-muted-foreground">{total}</span>
        </div>
    );
}
