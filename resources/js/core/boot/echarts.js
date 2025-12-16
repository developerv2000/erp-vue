import VChart from 'vue-echarts';
import { use } from 'echarts/core';

// ----- Import only what app uses (tree-shaking) -----
// Chart types
import {
    BarChart,
    LineChart,
    PieChart
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
};

export const defaulLegendOptions = {
    itemGap: 12,
    itemWidth: 20,
    itemHeight: 14,
};

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
};

export const arcPieDefaultEmphasisOptions = {
    focus: "self",
};

export const arcPieDefaultTooltipOptions = {
    trigger: 'item',
};
