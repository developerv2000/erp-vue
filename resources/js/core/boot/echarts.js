import VChart from 'vue-echarts';
import { use } from 'echarts/core';

// ----- Import only what app uses (tree-shaking) -----
// Chart types
import {
    BarChart,
    LineChart,
    PieChart,
    TreemapChart,
} from 'echarts/charts'

// Components
import {
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    ToolboxComponent,
    GridComponent,
} from 'echarts/components'

// Renderer
import { CanvasRenderer } from 'echarts/renderers'

// Register modules globally
use([
    BarChart,
    LineChart,
    PieChart,
    TreemapChart,
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    ToolboxComponent,
    GridComponent,
    CanvasRenderer
])

export default VChart

/*
|--------------------------------------------------------------------------
| Default chart options
|--------------------------------------------------------------------------
*/

export const defaultTextStyleOptions = {
    fontFamily: ['Fira Sans', 'sans-serif'],
}

export const defaultTitletOptions = {
    padding: [28, 20, 20, 20],
    textStyle: {
        fontSize: 15,
        fontWeight: '500',
    },
}

export const defaulLegendOptions = {
    itemGap: 12,
    itemWidth: 20,
    itemHeight: 14,
}

// Arc pie default options
export const arcPieDefaultSeriesOptions = {
    type: "pie",
    radius: ["40%", "100%"],
    center: ["50%", "340px"],
    startAngle: 180,
    endAngle: 360,
}

export const arcPieDefaultToolboxOptions = {
    feature: {
        saveAsImage: { show: true, pixelRatio: 3 },
    },
}

export const arcPieDefaultEmphasisOptions = {
    focus: "self",
}

export const arcPieDefaultTooltipOptions = {
    trigger: 'item',
}

// Graph default options (bars + lines)
export const graphDefaultGridOptions = {
    top: '86px',
    right: '56px',
    bottom: '80px',
    left: '56px',
}

export const graphDefaultTooltipOptions = {
    trigger: 'axis',
    axisPointer: {
        type: 'cross',
    }
}

export const graphDefaultXAxisPointerOptions = {
    type: "shadow",
}

export const graphDefaultToolboxOptions = {
    feature: {
        magicType: { show: true, type: ['line', 'bar'] }, // Enable switch between line and bar
        restore: { show: true },
        saveAsImage: { show: true, pixelRatio: 3 },
    },
}

export const gprahLineSeriesDefaultLabelOptions = {
    show: true,
    position: "top",
    distance: 16,
    align: "center",
    verticalAlign: "middle",
}

export const graphBarSeriesDefaultLabelOptions = {
    show: true,
    position: "top",
    rotate: 90,
    distance: 16,
    align: "center",
    verticalAlign: "middle",
}

// Treemap
export const treemapDefaultSeriesOptions = {
    type: "treemap",
    label: {
        formatter: (params) => {
            return `${params.name}: ${params.value}`;
        },
    },
    top: "86px",
    right: "24px",
    bottom: "32px",
    left: "24px",
    // roam: false, // Disable zoom and pan
    itemStyle: {
        borderWidth: 1,
    },
}

export const treemapDefaultToolboxOptions = {
    feature: {
        restore: { show: true },
        saveAsImage: { show: true, pixelRatio: 3 },
    },
}
