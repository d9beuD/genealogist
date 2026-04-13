import { Controller } from '@hotwired/stimulus';

const SVG_NS = 'http://www.w3.org/2000/svg';
const CARD_WIDTH = 280;
const CARD_HEIGHT = 76;
const AVATAR_SIZE = 44;
const HORIZONTAL_GAP = 32;
const LEVEL_GAP = 112;
const PADDING = 48;
const CONNECTOR_STEM = 18;
const MIN_ZOOM = 0.5;
const MAX_ZOOM = 2.5;
const ZOOM_STEP = 0.2;

export default class extends Controller {
    static targets = ['data', 'svg', 'status', 'viewport'];

    static values = {
        title: String,
    };

    connect() {
        this.zoom = 1;
        this.tree = JSON.parse(this.dataTarget.textContent);
        this.theme = this.readTheme();
        this.render();
    }

    zoomIn() {
        this.updateZoom(this.zoom + ZOOM_STEP);
    }

    zoomOut() {
        this.updateZoom(this.zoom - ZOOM_STEP);
    }

    zoomReset() {
        this.updateZoom(1);
    }

    async exportSvg() {
        this.setStatus('Preparing SVG export...');

        const { serialized } = await this.createExportPayload();

        this.downloadBlob(
            new Blob([serialized], { type: 'image/svg+xml;charset=utf-8' }),
            `${this.filenameBase}.svg`,
        );

        this.setStatus('SVG export ready.');
    }

    async exportPng() {
        await this.exportRaster('png');
    }

    async exportWebp() {
        await this.exportRaster('webp');
    }

    render() {
        this.layout = this.createLayout(this.tree);

        this.svgTarget.replaceChildren();
        this.svgTarget.setAttribute('xmlns', SVG_NS);
        this.svgTarget.setAttribute('viewBox', `0 0 ${this.layout.width} ${this.layout.height}`);
        this.svgTarget.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        this.svgTarget.setAttribute('font-family', this.theme.fontFamily);
        this.svgTarget.setAttribute('font-size', '16');
        const defs = this.createElement('defs');
        this.svgTarget.append(defs);

        this.layout.unions.forEach((union) => {
            this.svgTarget.append(this.renderUnion(union));
        });

        this.layout.nodes.forEach((entry) => {
            this.svgTarget.append(this.renderNode(entry, defs));
        });

        this.updateZoom(this.zoom);
    }

    createLayout(root) {
        const measuredTree = this.measure(root);
        const maxDepth = this.maxDepth(root);
        const nodes = [];
        const unions = [];

        this.positionNode(root, measuredTree, PADDING + measuredTree.width / 2, 0, maxDepth, nodes, unions);

        return {
            nodes,
            unions,
            width: measuredTree.width + PADDING * 2,
            height: maxDepth * LEVEL_GAP + CARD_HEIGHT + PADDING * 2,
        };
    }

    measure(node) {
        if (!node.parentUnion?.parents?.length) {
            return { width: CARD_WIDTH, parentMeasures: [] };
        }

        const parentMeasures = node.parentUnion.parents.map((parent) => this.measure(parent));
        const parentsWidth = this.totalChildrenWidth(parentMeasures);

        return {
            width: Math.max(CARD_WIDTH, parentsWidth),
            parentMeasures,
        };
    }

    positionNode(node, measuredNode, centerX, generation, maxDepth, nodes, unions) {
        const levelFromTop = maxDepth - generation - 1;
        const entry = {
            node,
            x: centerX,
            y: PADDING + levelFromTop * LEVEL_GAP + CARD_HEIGHT / 2,
        };

        nodes.push(entry);

        if (!node.parentUnion?.parents?.length) {
            return entry;
        }

        const parentEntries = [];
        const totalWidth = this.totalChildrenWidth(measuredNode.parentMeasures);
        let cursor = centerX - totalWidth / 2;

        node.parentUnion.parents.forEach((parent, index) => {
            const parentMeasure = measuredNode.parentMeasures[index];
            const parentCenterX = cursor + parentMeasure.width / 2;
            parentEntries.push(this.positionNode(parent, parentMeasure, parentCenterX, generation + 1, maxDepth, nodes, unions));
            cursor += parentMeasure.width + HORIZONTAL_GAP;
        });

        unions.push({
            union: node.parentUnion,
            child: entry,
            parents: parentEntries,
        });

        return entry;
    }

    renderUnion(entry) {
        const group = this.createElement('g', {
            class: 'tree-svg-union',
            fill: 'none',
            stroke: this.theme.linkColor,
            'stroke-linecap': 'round',
            'stroke-width': '4',
        });

        const parentBaseline = entry.parents[0].y + CARD_HEIGHT / 2 + CONNECTOR_STEM;
        const childTop = entry.child.y - CARD_HEIGHT / 2;
        const horizontalStart = Math.min(...entry.parents.map((parent) => parent.x));
        const horizontalEnd = Math.max(...entry.parents.map((parent) => parent.x));

        entry.parents.forEach((parent) => {
            group.append(this.createElement('line', {
                x1: parent.x,
                x2: parent.x,
                y1: parent.y + CARD_HEIGHT / 2,
                y2: parentBaseline,
            }));
        });

        if (entry.parents.length > 1) {
            group.append(this.createElement('line', {
                x1: horizontalStart,
                x2: horizontalEnd,
                y1: parentBaseline,
                y2: parentBaseline,
            }));
        }

        group.append(this.createElement('line', {
            x1: entry.child.x,
            x2: entry.child.x,
            y1: parentBaseline,
            y2: childTop,
        }));

        if (entry.union.startsAtLabel) {
            group.append(this.renderUnionLabel(entry.union.startsAtLabel, entry.child.x, parentBaseline, childTop));
        }

        return group;
    }

    renderUnionLabel(label, x, baselineY, childTop) {
        const group = this.createElement('g', {
            class: 'tree-svg-union-label',
        });
        const width = Math.max(84, label.length * 7 + 18);
        const height = 24;
        const y = baselineY + (childTop - baselineY - height) / 2;

        group.append(this.createElement('rect', {
            x: x - width / 2,
            y,
            width,
            height,
            rx: height / 2,
            fill: this.theme.bodyBackground,
            stroke: this.theme.borderColor,
            'stroke-width': '1',
        }));

        group.append(this.createElement('text', {
            x,
            y: y + 16,
            'text-anchor': 'middle',
            'font-size': '12',
            'font-weight': '600',
            fill: this.theme.secondaryColor,
        }, label));

        return group;
    }

    renderNode(entry, defs) {
        const group = this.createElement('a', {
            href: entry.node.profileUrl,
            class: 'tree-svg-node-link text-decoration-none',
        });

        const nodeGroup = this.createElement('g', {
            class: 'tree-svg-node',
            transform: `translate(${entry.x - CARD_WIDTH / 2} ${entry.y - CARD_HEIGHT / 2})`,
        });

        nodeGroup.append(this.createElement('rect', {
            width: CARD_WIDTH,
            height: CARD_HEIGHT,
            rx: '32',
            fill: this.theme.bodyBackground,
            stroke: this.theme.borderColor,
            'stroke-width': '1',
        }));

        if (entry.node.portraitUrl) {
            const clipPathId = `tree-portrait-${entry.node.occurrenceId}`;
            const clipPath = this.createElement('clipPath', { id: clipPathId });
            clipPath.append(this.createElement('circle', {
                cx: 34,
                cy: 38,
                r: AVATAR_SIZE / 2,
            }));
            defs.append(clipPath);

            nodeGroup.append(this.createElement('image', {
                x: 12,
                y: 16,
                width: AVATAR_SIZE,
                height: AVATAR_SIZE,
                href: entry.node.portraitUrl,
                'data-portrait-url': entry.node.portraitUrl,
                'clip-path': `url(#${clipPathId})`,
                preserveAspectRatio: 'xMidYMid slice',
            }));
        } else {
            nodeGroup.append(this.createElement('circle', {
                cx: 34,
                cy: 38,
                r: AVATAR_SIZE / 2,
                fill: this.placeholderColor(entry.node.gender),
            }));

            nodeGroup.append(this.createElement('text', {
                x: 34,
                y: 44,
                'text-anchor': 'middle',
                'font-size': '16',
                'font-weight': '700',
                fill: '#fff',
            }, this.initials(entry.node.fullName)));
        }

        nodeGroup.append(this.createElement('text', {
            x: 68,
            y: 33,
            'font-size': '15',
            'font-weight': '700',
            fill: this.theme.emphasisColor,
        }, this.truncate(entry.node.fullName, 28)));

        if (entry.node.yearsLabel) {
            nodeGroup.append(this.createElement('text', {
                x: 68,
                y: 54,
                'font-size': '13',
                fill: this.theme.secondaryColor,
            }, entry.node.yearsLabel));
        }

        group.append(nodeGroup);

        return group;
    }

    async exportRaster(format) {
        const label = format.toUpperCase();
        this.setStatus(`Preparing ${label} export...`);

        const { serialized, width, height } = await this.createExportPayload();
        const svgUrl = URL.createObjectURL(new Blob([serialized], { type: 'image/svg+xml;charset=utf-8' }));

        try {
            const image = await this.loadImage(svgUrl);
            const scale = 2;
            const canvas = document.createElement('canvas');
            canvas.width = Math.ceil(width * scale);
            canvas.height = Math.ceil(height * scale);

            const context = canvas.getContext('2d');
            if (!context) {
                throw new Error('Canvas rendering is not available in this browser.');
            }

            context.scale(scale, scale);
            context.drawImage(image, 0, 0, width, height);

            const blob = await new Promise((resolve) => {
                canvas.toBlob(resolve, `image/${format}`);
            });

            if (!blob) {
                throw new Error(`${label} export is not supported in this browser.`);
            }

            this.downloadBlob(blob, `${this.filenameBase}.${format}`);
            this.setStatus(`${label} export ready.`);
        } finally {
            URL.revokeObjectURL(svgUrl);
        }
    }

    async createExportPayload() {
        const clone = this.svgTarget.cloneNode(true);
        clone.setAttribute('xmlns', SVG_NS);
        await this.inlinePortraits(clone);

        return {
            serialized: new XMLSerializer().serializeToString(clone),
            width: Number.parseFloat(clone.getAttribute('width') ?? `${this.layout.width}`),
            height: Number.parseFloat(clone.getAttribute('height') ?? `${this.layout.height}`),
        };
    }

    async inlinePortraits(svg) {
        const images = Array.from(svg.querySelectorAll('image[data-portrait-url]'));

        await Promise.all(images.map(async (image) => {
            const portraitUrl = image.getAttribute('data-portrait-url');

            if (!portraitUrl) {
                return;
            }

            const dataUrl = await this.fetchPortraitDataUrl(portraitUrl);

            if (dataUrl) {
                image.setAttribute('href', dataUrl);
            }

            image.removeAttribute('data-portrait-url');
        }));
    }

    async fetchPortraitDataUrl(url) {
        try {
            const image = await this.loadImage(url);
            const canvas = document.createElement('canvas');
            canvas.width = 96;
            canvas.height = 96;

            const context = canvas.getContext('2d');
            if (!context) {
                return null;
            }

            context.drawImage(image, 0, 0, canvas.width, canvas.height);

            return canvas.toDataURL('image/jpeg', 0.82);
        } catch (error) {
            return null;
        }
    }

    loadImage(src) {
        return new Promise((resolve, reject) => {
            const image = new Image();
            image.crossOrigin = 'anonymous';
            image.onload = () => resolve(image);
            image.onerror = () => reject(new Error(`Unable to load image: ${src}`));
            image.src = src;
        });
    }

    updateZoom(nextZoom) {
        this.zoom = Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, Number.parseFloat(nextZoom.toFixed(2))));
        this.svgTarget.setAttribute('width', `${Math.ceil(this.layout.width * this.zoom)}`);
        this.svgTarget.setAttribute('height', `${Math.ceil(this.layout.height * this.zoom)}`);
    }

    totalChildrenWidth(parentMeasures) {
        if (!parentMeasures.length) {
            return CARD_WIDTH;
        }

        return parentMeasures.reduce((total, measure) => total + measure.width, 0) + HORIZONTAL_GAP * (parentMeasures.length - 1);
    }

    maxDepth(node) {
        if (!node.parentUnion?.parents?.length) {
            return 1;
        }

        return 1 + Math.max(...node.parentUnion.parents.map((parent) => this.maxDepth(parent)));
    }

    initials(fullName) {
        return fullName
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part[0])
            .join('');
    }

    truncate(value, length) {
        if (value.length <= length) {
            return value;
        }

        return `${value.slice(0, length - 1)}...`;
    }

    placeholderColor(gender) {
        if (gender === 0) {
            return '#b6608d';
        }

        if (gender === 1) {
            return '#4c7ea4';
        }

        return '#6c757d';
    }

    createElement(name, attributes = {}, textContent = null) {
        const element = document.createElementNS(SVG_NS, name);

        Object.entries(attributes).forEach(([key, value]) => {
            element.setAttribute(key, `${value}`);
        });

        if (null !== textContent) {
            element.textContent = textContent;
        }

        return element;
    }

    downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.append(link);
        link.click();
        link.remove();
        window.setTimeout(() => URL.revokeObjectURL(url), 0);
    }

    setStatus(message) {
        if (this.hasStatusTarget) {
            this.statusTarget.textContent = message;
        }
    }

    get filenameBase() {
        return this.titleValue
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '') || 'family-tree';
    }

    readTheme() {
        const styles = getComputedStyle(document.documentElement);

        return {
            bodyBackground: this.cssVar(styles, '--bs-body-bg', '#ffffff'),
            borderColor: this.cssVar(styles, '--bs-border-color', '#ced4da'),
            secondaryColor: this.cssVar(styles, '--bs-secondary-color', '#6c757d'),
            emphasisColor: this.cssVar(styles, '--bs-emphasis-color', '#212529'),
            fontFamily: this.cssVar(styles, '--bs-body-font-family', getComputedStyle(document.body).fontFamily || 'sans-serif'),
            linkColor: this.opaqueColor(this.cssVar(styles, '--bs-border-color', '#ced4da')),
        };
    }

    cssVar(styles, name, fallback) {
        return styles.getPropertyValue(name).trim() || fallback;
    }

    opaqueColor(color) {
        const rgbaMatch = color.match(/^rgba\((\d+),\s*(\d+),\s*(\d+),\s*([\d.]+)\)$/i);

        if (rgbaMatch) {
            return `rgb(${rgbaMatch[1]}, ${rgbaMatch[2]}, ${rgbaMatch[3]})`;
        }

        return color;
    }
}
