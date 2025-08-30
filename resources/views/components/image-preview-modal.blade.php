<div x-data="imagePreviewLightbox()" x-init="init()">
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()"
         @keydown.escape.window="close()"
         class="image-preview-lightbox-overlay"
         x-cloak>
        
        <div class="image-preview-lightbox-container" @click.stop>
            <button type="button" @click.stop="close()" class="image-preview-lightbox-close">
                <x-filament::icon icon="heroicon-o-x-mark" />
            </button>
            
            <div class="image-preview-lightbox-image-wrapper">
                <img :src="currentImage.url" 
                     :alt="getDisplayName()"
                     class="image-preview-lightbox-image">
                <div class="image-preview-lightbox-info" x-show="getDisplayName() || currentImage.link">
                    <p class="image-preview-lightbox-title" x-text="getDisplayName()" x-show="getDisplayName()"></p>
                    <p class="image-preview-lightbox-link" x-show="currentImage.link">
                        <a :href="currentImage.link" target="_blank" x-text="currentImage.link" class="text-blue-400 hover:text-blue-300 underline"></a>
                    </p>
                </div>
            </div>
            <template x-if="images && images.length > 1">
                <div>
                    <button type="button" @click.stop.prevent="previous()" class="image-preview-lightbox-nav prev">
                        <x-filament::icon icon="heroicon-o-chevron-left" />
                    </button>
                    
                    <button type="button" @click.stop.prevent="next()" class="image-preview-lightbox-nav next">
                        <x-filament::icon icon="heroicon-o-chevron-right" />
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    
    .image-preview-lightbox-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9999 !important;
        background-color: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 80px;
    }

    .image-preview-lightbox-container {
        position: relative;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999 !important;
    }

    .image-preview-lightbox-close {
        position: absolute;
        top: -50px;
        right: 0;
        color: white;
        background: none;
        border: none;
        cursor: pointer;
        padding: 10px;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .image-preview-lightbox-close:hover {
        opacity: 1;
    }

    .image-preview-lightbox-close svg {
        width: 32px;
        height: 32px;
    }

    .image-preview-lightbox-image-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #1f2937;
        border-radius: 8px;
        overflow: hidden;
        max-width: 90vw;
        max-height: 85vh;
    }

    .image-preview-lightbox-image {
        max-width: 100%;
        max-height: 85vh;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
    }

    .image-preview-lightbox-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
        border: none;
        border-radius: 50%;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .image-preview-lightbox-nav:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }

    .image-preview-lightbox-nav.prev {
        left: -60px;
    }

    .image-preview-lightbox-nav.next {
        right: -60px;
    }

    .image-preview-lightbox-nav svg {
        width: 24px;
        height: 24px;
    }

    .image-preview-lightbox-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
        padding: 24px;
        color: white;
        border-radius: 0 0 8px 8px;
    }

    .image-preview-lightbox-title {
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 8px 0;
    }

    .image-preview-lightbox-link {
        font-size: 14px;
        opacity: 0.9;
        margin: 0;
        line-height: 1.5;
    }
</style>

<script>
function imagePreviewLightbox() {
    return {
        isOpen: false,
        currentIndex: 0,
        images: [],
        currentImage: {
            url: '',
            name: {},
            link: '',
            filename: ''
        },
        
        getDisplayName() {
            return this.currentImage.name || this.currentImage.filename || '';
        },
        
        init() {
            const self = this;
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('image-preview-trigger')) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.openFromTable(e.target);
                }
            }, true);
            document.addEventListener('keydown', (e) => {
                if (this.isOpen) {
                    if (e.key === 'ArrowLeft') {
                        e.preventDefault();
                        this.previous();
                    } else if (e.key === 'ArrowRight') {
                        e.preventDefault();
                        this.next();
                    }
                }
            });
        },
        
        openFromTable(imageElement) {
            const rows = document.querySelectorAll('tbody tr');
            this.images = [];
            let clickedIndex = 0;
            
            rows.forEach((row) => {
                const imgContainer = row.querySelector('.fi-ta-image');
                if (imgContainer) {
                    const imgs = imgContainer.querySelectorAll('.image-preview-trigger');
                    
                    imgs.forEach((img) => {
                        const imageData = {
                            url: img.src,
                            name: img.alt || '',
                            link: '',
                            filename: img.alt || ''
                        };
                        
                        this.images.push(imageData);
                        
                        if (img === imageElement) {
                            clickedIndex = this.images.length - 1;
                        }
                    });
                }
            });
            
            if (this.images.length > 0) {
                this.currentIndex = clickedIndex;
                this.updateCurrentImage();
                this.open();
            }
        },
        
        open() {
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },
        
        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },
        
        next() {
            if (this.images.length > 0) {
                this.currentIndex = (this.currentIndex + 1) % this.images.length;
                this.updateCurrentImage();
            }
        },
        
        previous() {
            if (this.images.length > 0) {
                this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
                this.updateCurrentImage();
            }
        },
        
        updateCurrentImage() {
            if (this.images[this.currentIndex]) {
                this.currentImage = this.images[this.currentIndex];
            }
        }
    };
}
</script>